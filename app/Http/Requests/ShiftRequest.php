<?php

namespace App\Http\Requests;

use App\Models\Shift;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ShiftRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_shifts') ?? false;
    }

    public function rules(): array
    {
        $shift = $this->route('shift');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('shifts', 'name')->ignore($shift?->id)->whereNull('deleted_at'),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'different:start_time'],
            'description' => ['nullable', 'string', 'max:1000'],
            'active' => ['required', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                if ($validator->errors()->isNotEmpty() || ! $this->boolean('active')) {
                    return;
                }

                $shift = $this->route('shift');
                $overlaps = Shift::query()
                    ->where('active', true)
                    ->when($shift, fn ($query) => $query->whereKeyNot($shift->id))
                    ->get(['start_time', 'end_time'])
                    ->contains(fn (Shift $existing) => $this->overlaps(
                        $this->input('start_time'),
                        $this->input('end_time'),
                        $existing->start_time,
                        $existing->end_time,
                    ));

                if ($overlaps) {
                    $validator->errors()->add('start_time', 'This shift overlaps an existing active shift.');
                }
            },
        ];
    }

    private function overlaps(string $start, string $end, string $otherStart, string $otherEnd): bool
    {
        foreach ($this->timeSegments($start, $end) as [$startMinute, $endMinute]) {
            foreach ($this->timeSegments($otherStart, $otherEnd) as [$otherStartMinute, $otherEndMinute]) {
                if ($startMinute < $otherEndMinute && $otherStartMinute < $endMinute) {
                    return true;
                }
            }
        }

        return false;
    }

    private function timeSegments(string $start, string $end): array
    {
        $startMinute = $this->minutes($start);
        $endMinute = $this->minutes($end);

        return $endMinute > $startMinute
            ? [[$startMinute, $endMinute]]
            : [[$startMinute, 1440], [0, $endMinute]];
    }

    private function minutes(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return ($hours * 60) + $minutes;
    }
}
