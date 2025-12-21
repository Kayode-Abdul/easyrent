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
            'apartmentType' => 'required|string|max:100',
            'tenantId' => 'nullable|string|exists:users,user_id',
            'duration' => 'required|numeric|min:0.01',
            'fromDate' => 'required|date',
            'toDate' => 'required|date|after:fromDate',
            'price' => 'required|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'propertyId.required' => 'Property ID is required',
            'propertyId.exists' => 'Selected property does not exist',
            'apartmentType.required' => 'Apartment type is required',
            'apartmentType.string' => 'Apartment type must be a string',
            'apartmentType.max' => 'Apartment type cannot exceed 100 characters',
            'tenantId.exists' => 'Selected tenant does not exist',
            'duration.required' => 'Duration is required',
            'duration.numeric' => 'Duration must be a number',
            'duration.min' => 'Duration must be greater than 0',
            'fromDate.required' => 'Start date is required',
            'fromDate.date' => 'Invalid start date format',
            'toDate.required' => 'End date is required',
            'toDate.date' => 'Invalid end date format',
            'toDate.after' => 'End date must be after start date',
            'price.required' => 'Price is required',
            'price.numeric' => 'Price must be a number',
            'price.min' => 'Price must be greater than or equal to 0',
        ];
    }
}
