<?php

namespace App\Http\Controllers;

use App\Models\Amenity;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AmenityController extends Controller
{
    public function index()
    {
        $amenities = Amenity::all();
        return response()->json($amenities);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities',
            'icon' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000'
        ]);

        $amenity = Amenity::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Amenity created successfully',
            'amenity' => $amenity
        ]);
    }

    public function attachToProperty(Request $request, Property $property)
    {
        if ($property->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'amenity_ids' => 'required|array',
            'amenity_ids.*' => 'exists:amenities,id'
        ]);

        $property->amenities()->sync($request->amenity_ids);

        return response()->json([
            'success' => true,
            'message' => 'Amenities updated successfully',
            'property' => $property->load('amenities')
        ]);
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();

        return response()->json([
            'success' => true,
            'message' => 'Amenity deleted successfully'
        ]);
    }
}