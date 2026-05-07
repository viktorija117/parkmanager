<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use App\Services\ReservationPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'             => ['required', Rule::in(['single', 'multiple', 'full_week'])],
            'dates'            => ['required_if:type,multiple', 'array', 'min:1', 'max:5'],
            'dates.*'          => ['date', 'after_or_equal:today'],
            'date'             => ['required_if:type,single', 'date', 'after_or_equal:today'],
            'week_start'       => ['required_if:type,full_week', 'date'],
            'parking_space_id' => ['nullable', 'exists:parking_spaces,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            foreach ($this->resolvedDates() as $date) {
                $d = Carbon::parse($date);

                if (!ReservationPolicy::canReserve($d)) {
                    $validator->errors()->add('dates', __('reservations.invalid_date', ['date' => $date]));
                    continue;
                }

                $exists = Reservation::confirmed()
                    ->where('user_id', $this->user()->id)
                    ->whereDate('date', $d)
                    ->exists();

                if ($exists) {
                    $validator->errors()->add('dates', __('reservations.already_reserved', ['date' => $date]));
                }
            }
        });
    }

    public function resolvedDates(): array
    {
        return match ($this->type) {
            'single'    => [$this->date],
            'multiple'  => $this->dates ?? [],
            'full_week' => collect(range(0, 4))
                ->map(fn ($i) => Carbon::parse($this->week_start)->addDays($i)->toDateString())
                ->all(),
            default => [],
        };
    }
}
