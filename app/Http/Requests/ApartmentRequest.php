<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'propertyId' => 'required|exists:properties,property_id',
            'tenantId.*' => 'nullable|string',
            'fromRange.*' => 'nullable|date',
            'toRange.*' => 'nullable|date|after:fromRange.*',
            'amount.*' => 'required|numeric|min:0',
            'rentalType.*' => 'required|in:hourly,daily,weekly,monthly,quarterly,semi_annually,yearly,bi_annually',
        ];
    }

    public function messages(): array
    {
        return [
            'propertyId.required' => 'Property ID is required',
            'propertyId.exists' => 'Selected property does not exist',
            'tenantId.*.string' => 'Tenant ID must be a string',
            'fromRange.*.date' => 'Invalid start date format',
            'toRange.*.date' => 'Invalid end date format',
            'toRange.*.after' => 'End date must be after start date',
            'amount.*.required' => 'Amount is required for each apartment',
            'amount.*.numeric' => 'Amount must be a number',
            'amount.*.min' => 'Amount must be greater than 0',
            'rentalType.*.required' => 'Rental type is required for each apartment',
            'rentalType.*.in' => 'Invalid rental type selected'
        ];
    }
}