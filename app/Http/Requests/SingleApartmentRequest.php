<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SingleApartmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'propertyId' => 'required|exists:properties,property_id',
            'apartmentType' => 'nullable|string|max:100',
            'tenantId' => 'nullable|string',
            'duration' => 'required|numeric|min:0.01',
            'fromRange' => 'required|date',
            'toRange' => 'required|date|after:fromRange',
            'amount' => 'required|numeric|min:0',
            'rentalType' => 'required|in:hourly,daily,weekly,monthly,quarterly,semi_annually,yearly,bi_annually',
            'currency_id' => 'nullable|exists:currencies,id',
        ];
    }

    public function messages(): array
    {
        return [
            'propertyId.required' => 'Property ID is required',
            'propertyId.exists' => 'Selected property does not exist',
            'apartmentType.string' => 'Apartment type must be a string',
            'apartmentType.max' => 'Apartment type cannot exceed 100 characters',
            'tenantId.string' => 'Tenant ID must be a string',
            'duration.required' => 'Duration is required',
            'duration.numeric' => 'Duration must be a number',
            'duration.min' => 'Duration must be greater than 0',
            'fromRange.required' => 'Start date is required',
            'fromRange.date' => 'Invalid start date format',
            'toRange.required' => 'End date is required',
            'toRange.date' => 'Invalid end date format',
            'toRange.after' => 'End date must be after start date',
            'amount.required' => 'Price is required',
            'amount.numeric' => 'Price must be a number',
            'amount.min' => 'Price must be greater than or equal to 0',
            'rentalType.required' => 'Rental type is required',
            'rentalType.in' => 'Invalid rental type selected',
        ];
    }
}
