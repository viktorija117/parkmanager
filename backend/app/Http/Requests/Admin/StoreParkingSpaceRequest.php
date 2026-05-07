<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParkingSpaceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $spaceId = $this->route('space')?->id;

        return [
            'office_id' => ['required', 'exists:offices,id'],
            'label'     => [
                'required',
                'string',
                'max:20',
                Rule::unique('parking_spaces')
                    ->where('office_id', $this->office_id)
                    ->ignore($spaceId),
            ],
            'row'       => ['nullable', 'string', 'max:30'],
            'notes'     => ['nullable', 'string', 'max:255'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
