# Cashier Variance and HR Integration

## Deployment order

1. Back up the HR and Finance databases.
2. Deploy HR first, run `composer install --no-dev --optimize-autoloader`, then run `php artisan migrate --force`.
3. Configure the HR API IP allowlist for the Finance server. Prefer loopback (`127.0.0.1,::1`) when both applications share a host.
4. From the HR application directory, issue the scoped Finance token:

   ```bash
   php artisan hr:issue-finance-token
   ```

   Copy the displayed token immediately into Finance's `HR_API_TOKEN`. HR stores only a hash, so the plaintext cannot be recovered later. To replace an existing token:

   ```bash
   php artisan hr:issue-finance-token --rotate
   ```
5. Configure Finance:

   ```dotenv
   HR_API_BASE_URL=https://127.0.0.1
   HR_API_TOKEN=<secret>
   HR_API_CASHIERS_PATH=/api/v1/integration/cashiers
   HR_API_CONNECT_TIMEOUT=3
   HR_API_TIMEOUT=10
   HR_CASHIER_CACHE_STALE_AFTER=3600
   ```

6. Deploy Finance and run `php artisan migrate --force`.
7. Seed new permissions with `php artisan db:seed --class=PermissionSeeder --force` followed by `php artisan db:seed --class=RolePermissionSeeder --force`.
8. Run `php artisan hr:sync-cashiers`, verify the cashier-cache timestamp, then assign each active cashier to a Finance branch and default shift.

## Runtime requirements

Apache serves HTTP only. Do not run Artisan commands in a terminal session or every five minutes by hand. Use cron for the scheduler and Supervisor for the queue worker. Both survive logout and reboot.

In production set:

```dotenv
QUEUE_CONNECTION=database
```

`sync` will process HR jobs inside `schedule:run`. That can block the scheduler if HR is slow or down.

### Scheduler (cron)

Laravel needs `schedule:run` once a minute. It then runs `hr:sync-cashiers` hourly and `cashier-variances:process-outbox` every five minutes, with `withoutOverlapping()`.

As the application user:

```bash
crontab -e
```

```cron
* * * * * cd /var/www/finance && php artisan schedule:run >> /dev/null 2>&1
```

Replace `/var/www/finance` with the real project path and use the same PHP binary Apache uses (`which php`). Confirm with `php artisan schedule:list`.

### Queue worker (Supervisor)

The outbox command dispatches `ProcessCashierVarianceOutbox` jobs. Keep one worker running:

```bash
sudo apt install supervisor
```

Create `/etc/supervisor/conf.d/finance-worker.conf`:

```ini
[program:finance-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/finance/artisan queue:work database --sleep=1 --tries=5 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/finance/storage/logs/worker.log
stopwaitsecs=60
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start finance-worker:*
sudo supervisorctl status
```

After each deploy:

```bash
php artisan config:cache
php artisan queue:restart
```

The worker finishes the current job, then reloads new code.

### Other

- Use HTTPS even on the same server unless the connection is strictly loopback. Restrict the HR integration location at the web server/firewall and application allowlist.
- Store the token only in environment or secret-manager configuration. Never put it in source control or logs.
- The application user must be able to write `storage/` and `bootstrap/cache/` so schedule overlap locks work.

## Operations and recovery

- HR downtime does not clear Finance's cache. Existing cached/assigned cashiers remain available, and the assignment page shows cache age.
- Failed shortage delivery stays in the Finance outbox. Restore HR connectivity, then run `php artisan cashier-variances:process-outbox --retry-failed`; imports are idempotent by reconciliation UUID.
- Only approved positive shortages are sent to HR. HR receives them as pending items, so they do not affect payroll until HR approval.
- If a token is exposed, rotate it in HR, replace `HR_API_TOKEN` in Finance, clear configuration cache, and retry the outbox.
- If a reversal conflicts because HR already approved or payroll-processed the shortage, leave the Finance audit/ledger intact and resolve the HR item manually.

## Rollback

- Stop the scheduler and queue workers before rolling back application code.
- Do not drop variance, ledger, audit, outbox, or cashier-cache tables after production data exists. Restore the previous release while leaving forward-compatible tables in place.
- A database rollback is safe only before any reconciliation is approved/imported and should be performed from verified backups.
