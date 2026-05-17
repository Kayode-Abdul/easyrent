<?php

namespace App\Http\Controllers;

use App\Models\ArtisanRating;
use App\Models\ArtisanTask;
use Illuminate\Http\Request;

class ArtisanRatingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'artisan_id' => 'required|exists:users,user_id',
            'task_id' => 'nullable|exists:artisan_tasks,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id() ?? auth()->user()->user_id;

        // Check if rating already exists for this task by this user
        if ($request->task_id) {
            $existing = ArtisanRating::where('task_id', $request->task_id)
                ->where('user_id', $userId)
                ->first();
            
            if ($existing) {
                return response()->json(['success' => false, 'message' => 'You have already rated the artisan for this task.']);
            }
        }

        $rating = ArtisanRating::create([
            'artisan_id' => $request->artisan_id,
            'user_id' => $userId,
            'task_id' => $request->task_id,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        return response()->json(['success' => true, 'message' => 'Rating submitted successfully!', 'data' => $rating]);
    }
}
