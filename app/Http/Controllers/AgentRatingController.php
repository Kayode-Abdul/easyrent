<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AgentRating;
use App\Models\User;
use App\Models\Property;
use Illuminate\Support\Facades\Auth;

class AgentRatingController extends Controller
{
    // Submit a rating for an agent
    public function store(Request $request)
    {
        $request->validate([
            'agent_id' => 'required|exists:users,user_id',
            'property_id' => 'required|exists:properties,prop_id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $user = Auth::user();
        $agentId = $request->agent_id;
        $propertyId = $request->property_id;

        // Only allow if user is landlord of property or tenant of property
        $property = Property::where('prop_id', $propertyId)->first();
        if (!$property || ($property->user_id !== $user->user_id && $property->agent_id !== $agentId)) {
            return response()->json(['success' => false, 'message' => 'You are not eligible to rate this agent for this property.'], 403);
        }

        // Only one rating per user per property per agent
        $existing = AgentRating::where('agent_id', $agentId)
            ->where('user_id', $user->user_id)
            ->where('property_id', $propertyId)
            ->first();
        if ($existing) {
            return response()->json(['success' => false, 'message' => 'You have already rated this agent for this property.'], 409);
        }

        $rating = AgentRating::create([
            'agent_id' => $agentId,
            'user_id' => $user->user_id,
            'property_id' => $propertyId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'message' => 'Rating submitted successfully.', 'data' => $rating]);
    }

    // Fetch ratings for an agent
    public function show($agentId)
    {
        $ratings = AgentRating::where('agent_id', $agentId)
            ->with(['user:user_id,first_name,last_name,photo'])
            ->orderBy('created_at', 'desc')
            ->get();
        $average = AgentRating::where('agent_id', $agentId)->avg('rating');
        return response()->json([
            'success' => true,
            'average' => round($average, 2),
            'ratings' => $ratings
        ]);
    }
}
