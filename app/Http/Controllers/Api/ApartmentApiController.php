<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Apartment;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ApartmentApiController extends Controller
{
    /**
     * Get all apartments with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 15), 100);
        
        $apartments = Apartment::with(['property:id,prop_id,address,state,lga', 'tenant:user_id,first_name,last_name'])
            ->when($request->get('property_id'), function ($query, $propertyId) {
                return $query->where('property_id', $propertyId);
            })
            ->when($request->get('occupied'), function ($query, $occupied) {
                return $query->where('occupied', $occupied === 'true');
            })
            ->when($request->get('available'), function ($query) {
                return $query->where('occupied', false);
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $apartments->items(),
            'pagination' => [
                'current_page' => $apartments->currentPage(),
                'per_page' => $apartments->perPage(),
                'total' => $apartments->total(),
                'last_page' => $apartments->lastPage(),
                'has_more' => $apartments->hasMorePages()
            ]
        ]);
    }

    /**
     * Get specific apartment
     */
    public function show($id): JsonResponse
    {
        $apartment = Apartment::with([
                'property:id,prop_id,address,state,lga,user_id',
                'property.owner:user_id,first_name,last_name,email,phone',
                'tenant:user_id,first_name,last_name,email,phone'
            ])
            ->where('apartment_id', $id)
            ->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $apartment
        ]);
    }

    /**
     * Create new apartment (requires API key)
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'property_id' => 'required|exists:properties,prop_id',
            'apartment_type' => 'required|string|max:100',
            'user_id' => 'required|exists:users,user_id',
            'tenant_id' => 'nullable|exists:users,user_id',
            'amount' => 'nullable|numeric|min:0',
            'range_start' => 'nullable|date',
            'range_end' => 'nullable|date|after:range_start',
            'occupied' => 'boolean'
        ]);

        try {
            // Generate unique apartment ID
            do {
                $apartmentId = mt_rand(1000000, 9999999);
            } while (Apartment::where('apartment_id', $apartmentId)->exists());

            $apartment = Apartment::create([
                'apartment_id' => $apartmentId,
                'property_id' => $request->property_id,
                'apartment_type' => $request->apartment_type,
                'user_id' => $request->user_id,
                'tenant_id' => $request->tenant_id,
                'amount' => $request->amount,
                'range_start' => $request->range_start,
                'range_end' => $request->range_end,
                'occupied' => $request->occupied ?? false,
                'created_at' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Apartment created successfully',
                'data' => $apartment->load(['property', 'tenant'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create apartment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update apartment (requires API key)
     */
    public function update(Request $request, $id): JsonResponse
    {
        $apartment = Apartment::where('apartment_id', $id)->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found'
            ], 404);
        }

        $request->validate([
            'apartment_type' => 'sometimes|string|max:100',
            'tenant_id' => 'sometimes|nullable|exists:users,user_id',
            'amount' => 'sometimes|nullable|numeric|min:0',
            'range_start' => 'sometimes|nullable|date',
            'range_end' => 'sometimes|nullable|date|after:range_start',
            'occupied' => 'sometimes|boolean'
        ]);

        try {
            $apartment->update($request->only([
                'apartment_type', 'tenant_id', 'amount', 'range_start', 'range_end', 'occupied'
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Apartment updated successfully',
                'data' => $apartment->fresh(['property', 'tenant'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update apartment: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete apartment (requires API key)
     */
    public function destroy($id): JsonResponse
    {
        $apartment = Apartment::where('apartment_id', $id)->first();

        if (!$apartment) {
            return response()->json([
                'success' => false,
                'message' => 'Apartment not found'
            ], 404);
        }

        try {
            $apartment->delete();

            return response()->json([
                'success' => true,
                'message' => 'Apartment deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete apartment: ' . $e->getMessage()
            ], 500);
        }
    }
}