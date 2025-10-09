<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Property;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'property_id' => 'required|exists:properties,id',
            'check_in' => 'required|date|after:today',
            'check_out' => 'required|date|after:check_in',
            'special_requests' => 'nullable|string|max:500'
        ]);

        $property = Property::findOrFail($request->property_id);
        $totalDays = now()->parse($request->check_in)->diffInDays($request->check_out);
        $totalPrice = $property->price_per_night * $totalDays;

        $booking = Booking::create([
            'property_id' => $request->property_id,
            'user_id' => Auth::id(),
            'check_in' => $request->check_in,
            'check_out' => $request->check_out,
            'total_price' => $totalPrice,
            'special_requests' => $request->special_requests
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Booking created successfully',
            'booking' => $booking
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:confirmed,cancelled'
        ]);

        $booking->update(['status' => $request->status]);

        return response()->json([
            'success' => true,
            'message' => 'Booking updated successfully',
            'booking' => $booking
        ]);
    }

    public function index()
    {
        $bookings = Booking::where('user_id', Auth::id())
            ->with('property')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($bookings);
    }
}