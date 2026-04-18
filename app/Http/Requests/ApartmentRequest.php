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
        // Detect if this is a singular or array request
        $isSingular = !is_array($this->input('amount'));
        
        if ($isSingular) {
            // Singular field validation (from property show page)
            return [
                'propertyId' => 'required|exists:properties,property_id',
                'tenantId' => 'nullable|string',
                'fromRange' => 'required|date',
                'toRange' => 'required|date|after:fromRange',
                'amount' => 'required|numeric|min:0',
                'rentalType' => 'required|in:hourly,daily,weekly,monthly,quarterly,semi_annually,yearly,bi_annually',
                'duration' => 'nullable|numeric|min:0',
                'currency_id' => 'nullable|exists:currencies,id',
            ];
        } else {
            // Array field validation (from listing page bulk creation)
            return [
                'propertyId' => 'required|exists:properties,property_id',
                'tenantId.*' => 'nullable|string',
                'fromRange.*' => 'nullable|date',
                'toRange.*' => 'nullable|date|after:fromRange.*',
                'amount.*' => 'required|numeric|min:0',
                'rentalType.*' => 'required|in:hourly,daily,weekly,monthly,quarterly,semi_annually,yearly,bi_annually',
                'duration.*' => 'nullable|numeric|min:0',
                'currency_id.*' => 'nullable|exists:currencies,id',
            ];
        }
    }

    public function messages(): array
    {
        return [
            'propertyId.required' => 'Property ID is required',
            'propertyId.exists' => 'Selected property does not exist',
            
            // Singular field messages
            'tenantId.string' => 'Tenant ID must be a string',
            'fromRange.required' => 'Start date is required',
            'fromRange.date' => 'Invalid start date format',
            'toRange.required' => 'End date is required',
            'toRange.date' => 'Invalid end date format',
            'toRange.after' => 'End date must be after start date',
            'amount.required' => 'Price is required',
            'amount.numeric' => 'Price must be a number',
            'amount.min' => 'Price must be greater than 0',
            'rentalType.required' => 'Rental type is required',
            'rentalType.in' => 'Invalid rental type selected',
            'duration.numeric' => 'Duration must be a number',
            'duration.min' => 'Duration must be greater than 0',
            
            // Array field messages
            'tenantId.*.string' => 'Tenant ID must be a string',
            'fromRange.*.date' => 'Invalid start date format',
            'toRange.*.date' => 'Invalid end date format',
            'toRange.*.after' => 'End date must be after start date',
            'amount.*.required' => 'Amount is required for each apartment',
            'amount.*.numeric' => 'Amount must be a number',
            'amount.*.min' => 'Amount must be greater than 0',
            'rentalType.*.required' => 'Rental type is required for each apartment',
            'rentalType.*.in' => 'Invalid rental type selected',
            'duration.*.numeric' => 'Duration must be a number',
            'duration.*.min' => 'Duration must be greater than 0',
        ];
    }
}
