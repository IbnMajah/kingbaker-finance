<?php

namespace App\Services;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Throwable;

class HrApiClient
{
    public function activeCashiers(): Collection
    {
        $baseUrl = rtrim((string) config('services.hr.base_url'), '/');
        $token = (string) config('services.hr.token');
        $path = (string) config('services.hr.cashiers_path', '/api/v1/integration/cashiers');

        if ($baseUrl === '' || $token === '') {
            throw new RuntimeException('The HR integration is not configured.');
        }

        try {
            $cashiers = collect();
            $page = 1;

            do {
                $response = $this->request($baseUrl, $token)->get($path, [
                    'role' => 'Cashier',
                    'status' => 'active',
                    'page' => $page,
                ]);

                if (! $response->successful()) {
                    throw new RuntimeException('The HR service could not complete the cashier refresh.');
                }

                $payload = $response->json();
                if (! is_array($payload)) {
                    throw new RuntimeException('The HR service returned an invalid response.');
                }

                $items = data_get($payload, 'data.data', data_get($payload, 'data', data_get($payload, 'employees')));
                if (! is_array($items)) {
                    throw new RuntimeException('The HR service returned an invalid cashier list.');
                }

                $cashiers = $cashiers->concat($items);
                $lastPage = (int) data_get($payload, 'data.last_page', data_get($payload, 'meta.last_page', 1));
                $page++;
            } while ($page <= $lastPage && $page <= 100);

            if ($lastPage > 100) {
                throw new RuntimeException('The HR cashier response exceeded the safe page limit.');
            }

            return $cashiers;
        } catch (RuntimeException $exception) {
            throw $exception;
        } catch (Throwable) {
            throw new RuntimeException('The HR service is temporarily unavailable.');
        }
    }

    private function request(string $baseUrl, string $token): PendingRequest
    {
        $timeout = max(1, min((int) config('services.hr.timeout', 5), 10));
        $connectTimeout = max(1, min((int) config('services.hr.connect_timeout', 3), $timeout));

        return Http::baseUrl($baseUrl)
            ->acceptJson()
            ->withToken($token)
            ->connectTimeout($connectTimeout)
            ->timeout($timeout)
            ->retry(2, 200, throw: false);
    }
}
