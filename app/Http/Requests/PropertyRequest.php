<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PropertyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'propertyType' => 'required|integer|between:1,4',
            'address' => 'required|string|max:255',
            'state' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'aboveOne' => 'nullable|boolean'
        ];
    }

    public function messages(): array
    {
        return [
            'propertyType.required' => 'Please select a property type',
            'propertyType.between' => 'Invalid property type selected',
            'address.required' => 'Please enter the property address',
            'state.required' => 'Please select a state',
            'city.required' => 'Please select a city'
        ];
    }
} 