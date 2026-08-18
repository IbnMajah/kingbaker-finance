<?php

namespace App\Jobs;

use App\Models\CashierVarianceOutbox;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class ProcessCashierVarianceOutbox implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 5;

    public function __construct(public int $outboxId)
    {
    }

    public function backoff(): array
    {
        return [60, 300, 900, 3600];
    }

    public function handle(): void
    {
        $event = CashierVarianceOutbox::find($this->outboxId);

        if (!$event || $event->status === 'processed') {
            return;
        }

        $baseUrl = rtrim((string) config('services.hr.base_url'), '/');
        $token = (string) config('services.hr.token');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('HR integration is not configured.');
        }

        $event->update([
            'status' => 'processing',
            'attempts' => $event->attempts + 1,
            'last_error' => null,
        ]);

        try {
            $request = Http::baseUrl($baseUrl)
                ->acceptJson()
                ->asJson()
                ->withToken($token)
                ->connectTimeout((int) config('services.hr.connect_timeout', 3))
                ->timeout((int) config('services.hr.timeout', 10));

            $response = $event->event_type === 'shortage.reversed'
                ? $request->post(
                    "/api/v1/integration/cash-shortages/{$event->payload['source_reference']}/reversal",
                    ['reason' => $event->payload['reason']]
                )
                : $request->post('/api/v1/integration/cash-shortages', $event->payload);

            if ($response->clientError() && $response->status() !== 429) {
                $event->update([
                    'status' => 'failed',
                    'processed_at' => now(),
                    'external_reference' => $response->json('data.id'),
                    'last_error' => mb_substr("HR API returned HTTP {$response->status()}.", 0, 2000),
                ]);

                return;
            }

            if (!$response->successful()) {
                throw new RuntimeException("HR API returned HTTP {$response->status()}.");
            }

            $event->update([
                'status' => 'processed',
                'processed_at' => now(),
                'external_reference' => $response->json('data.id', $response->json('id')),
                'last_error' => null,
            ]);
        } catch (Throwable $exception) {
            $event->update([
                'status' => 'pending',
                'available_at' => now()->addSeconds($this->backoff()[min($event->attempts - 1, 3)]),
                'last_error' => mb_substr($exception->getMessage(), 0, 2000),
            ]);

            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        CashierVarianceOutbox::whereKey($this->outboxId)->update([
            'status' => 'failed',
            'last_error' => mb_substr($exception?->getMessage() ?? 'Unknown integration error.', 0, 2000),
        ]);
    }
}
