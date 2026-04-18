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
            'propertyType' => 'required|integer|between:1,10',
            'address' => 'required|string|max:255',
            'state' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state_id' => 'required|exists:states,id',
            'lga_id' => 'required|exists:lgas,id',
            'currency_id' => 'nullable|exists:currencies,id',
            'aboveOne' => 'nullable|boolean',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:5120'
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