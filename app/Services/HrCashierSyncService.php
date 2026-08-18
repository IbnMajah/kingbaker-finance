<?php

namespace App\Services;

use App\Models\HrCashier;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class HrCashierSyncService
{
    public function __construct(private readonly HrApiClient $client)
    {
    }

    public function sync(): int
    {
        $cashiers = $this->client->activeCashiers()
            ->map(fn ($employee) => $this->normalize($employee))
            ->keyBy('employee_id');

        $syncedAt = now();

        DB::transaction(function () use ($cashiers, $syncedAt) {
            foreach ($cashiers as $cashier) {
                HrCashier::updateOrCreate(
                    ['employee_id' => $cashier['employee_id']],
                    array_merge($cashier, [
                        'status' => 'active',
                        'last_seen_at' => $syncedAt,
                        'last_synced_at' => $syncedAt,
                    ])
                );
            }

            HrCashier::query()
                ->when($cashiers->isNotEmpty(), fn ($query) => $query->whereNotIn('employee_id', $cashiers->keys()))
                ->update([
                    'status' => 'inactive',
                    'last_synced_at' => $syncedAt,
                    'updated_at' => $syncedAt,
                ]);
        });

        Log::info('HR cashier cache refresh completed.', ['cashier_count' => $cashiers->count()]);

        return $cashiers->count();
    }

    private function normalize(mixed $employee): array
    {
        if (! is_array($employee)) {
            throw new RuntimeException('The HR service returned an invalid cashier record.');
        }

        $employeeId = data_get($employee, 'employee_id', data_get($employee, 'id'));
        $firstName = $this->nullableString(data_get($employee, 'first_name'));
        $lastName = $this->nullableString(data_get($employee, 'last_name'));
        $name = $this->nullableString(data_get($employee, 'name')) ?? trim("{$firstName} {$lastName}");
        $status = strtolower($this->nullableString(data_get($employee, 'status')) ?? 'active');
        $role = strtolower($this->nullableString(data_get(
            $employee,
            'role',
            data_get($employee, 'job_title.name', data_get($employee, 'job_title'))
        )) ?? 'cashier');

        if (! is_scalar($employeeId) || trim((string) $employeeId) === '' || trim((string) $name) === '') {
            throw new RuntimeException('The HR service returned an incomplete cashier record.');
        }

        if ($status !== 'active' || $role !== 'cashier') {
            throw new RuntimeException('The HR service returned an unexpected cashier result.');
        }

        return [
            'employee_id' => trim((string) $employeeId),
            'first_name' => $firstName,
            'last_name' => $lastName,
            'name' => trim((string) $name),
            'email' => $this->nullableString(data_get($employee, 'email')),
            'hr_branch_code' => $this->nullableString(data_get($employee, 'branch.code', data_get($employee, 'branch_code'))),
            'hr_branch_name' => $this->nullableString(data_get($employee, 'branch.name', data_get($employee, 'branch_name'))),
        ];
    }

    private function nullableString(mixed $value): ?string
    {
        return is_scalar($value) && trim((string) $value) !== '' ? trim((string) $value) : null;
    }
}
