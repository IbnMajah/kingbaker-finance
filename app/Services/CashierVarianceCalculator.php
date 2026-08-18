<?php

namespace App\Services;

use InvalidArgumentException;

class CashierVarianceCalculator
{
    /**
     * @return array{amount: string, classification: string}
     */
    public function calculate(string $expected, string $actual): array
    {
        $varianceCents = $this->toCents($expected) - $this->toCents($actual);

        return [
            'amount' => $this->fromCents($varianceCents),
            'classification' => match (true) {
                $varianceCents > 0 => 'shortage',
                $varianceCents < 0 => 'surplus',
                default => 'balanced',
            },
        ];
    }

    public function negate(string $amount): string
    {
        return $this->fromCents(-$this->toCents($amount));
    }

    public function format(float|string $amount): string
    {
        return $this->fromCents($this->toCents((string) $amount));
    }

    private function toCents(string $amount): int
    {
        $normalized = str_replace([',', ' '], '', trim($amount));

        if (! preg_match('/^-?\d+(?:\.\d{1,2})?$/', $normalized)) {
            throw new InvalidArgumentException('Amounts must contain no more than two decimal places.');
        }

        $negative = str_starts_with($normalized, '-');
        $unsigned = ltrim($normalized, '-');
        [$whole, $decimal] = array_pad(explode('.', $unsigned, 2), 2, '');
        $cents = ((int) $whole * 100) + (int) str_pad($decimal, 2, '0');

        return $negative ? -$cents : $cents;
    }

    private function fromCents(int $cents): string
    {
        $prefix = $cents < 0 ? '-' : '';
        $absolute = abs($cents);

        return sprintf('%s%d.%02d', $prefix, intdiv($absolute, 100), $absolute % 100);
    }
}
