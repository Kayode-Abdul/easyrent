<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PropertyApiController extends Controller
{
    /**
     * Get all properties with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100); // Max 100 per page
        
        $properties = Property::with(['owner:user_id,first_name,last_name,email', 'apartments'])
            ->when($request->get('state'), function ($query, $state) {
                return $query->where('state', $state);
            })
            ->when($request->get('lga'), function ($query, $lga) {
                return $query->where('lga', $lga);
            })
            ->when($request->get('type'), function ($query, $type) {
                return $query->where('prop_type', $type);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
                'last_page' => $properties->lastPage(),
                'has_more' => $properties->hasMorePages()
            ]
        ]);
    }

    /**
     * Get specific property
     */
    public function show($id): JsonResponse
    {
        $property = Property::with(['owner:user_id,first_name,last_name,email,phone', 'apartments.tenant:user_id,first_name,last_name'])
            ->where('prop_id', $id)
            ->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $property
        ]);
    }

    /**
     * Search properties
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q');
        $perPage = min($request->get('per_page', 15), 100);

        if (!$query) {
            return response()->json([
                'success' => false,
                'message' => 'Search query is required'
            ], 400);
        }

        $properties = Property::with(['owner:user_id,first_name,last_name'])
            ->where(function ($q) use ($query) {
                $q->where('address', 'LIKE', "%{$query}%")
                  ->orWhere('state', 'LIKE', "%{$query}%")
                  ->orWhere('lga', 'LIKE', "%{$query}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $properties->items(),
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'per_page' => $properties->perPage(),
                'total' => $properties->total(),
                'last_page' => $properties->lastPage(),
                'has_more' => $properties->hasMorePages()
            ]
        ]);
    }

    /**
     * Create new property (requires API key)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,user_id',
            'prop_type' => 'required|integer|between:1,4',
            'address' => 'required|string|max:500',
            'state' => 'required|string|max:100',
            'lga' => 'required|string|max:100',
            'no_of_apartment' => 'nullable|integer|min:1'
        ]);

        try {
            // Generate unique property ID
            do {
                $propId = mt_rand(1000000, 9999999);
            } while (Property::where('prop_id', $propId)->exists());

            $property = Property::create([
                'user_id' => $request->user_id,
                'prop_id' => $propId,
                'prop_type' => $request->prop_type,
                'address' => $request->address,
                'state' => $request->state,
                'lga' => $request->lga,
                'no_of_apartment' => $request->no_of_apartment,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Property created successfully',
                'data' => $property
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update property (requires API key)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $property = Property::where('prop_id', $id)->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        $request->validate([
            'prop_type' => 'sometimes|integer|between:1,4',
            'address' => 'sometimes|string|max:500',
            'state' => 'sometimes|string|max:100',
            'lga' => 'sometimes|string|max:100',
            'no_of_apartment' => 'sometimes|nullable|integer|min:1'
        ]);

        try {
            $property->update($request->only([
                'prop_type', 'address', 'state', 'lga', 'no_of_apartment'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Property updated successfully',
                'data' => $property->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update property: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete property (requires API key)
     */
    public function destroy($id): JsonResponse
    {
        $property = Property::where('prop_id', $id)->first();

        if (!$property) {
            return response()->json([
                'success' => false,
                'message' => 'Property not found'
            ], 404);
        }

        try {
            $property->delete();

            return response()->json([
                'success' => true,
                'message' => 'Property deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete property: ' . $e->getMessage()
            ], 500);
        }
    }
}