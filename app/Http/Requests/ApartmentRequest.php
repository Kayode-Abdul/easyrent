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
            'apartmentType' => 'required|string',
            'tenantId' => 'nullable|exists:users,user_id',
            'duration' => 'required|in:1,3,6,12',
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after:fromDate',
            'price' => 'required|numeric|min:0'
        ];
    }

    public function messages(): array
    {
        return [
            'propertyId.required' => 'Property ID is required',
            'propertyId.exists' => 'Selected property does not exist',
            'tenantId.required' => 'At least one apartment is required',
            'fromRange.*.required_with' => 'Start date is required when tenant is specified',
            'fromRange.*.date' => 'Invalid start date format',
            'toRange.*.required_with' => 'End date is required when tenant is specified',
            'toRange.*.date' => 'Invalid end date format',
            'toRange.*.after' => 'End date must be after start date',
            'amount.*.numeric' => 'Amount must be a number',
            'amount.*.min' => 'Amount must be greater than 0'
        ];
    }
}