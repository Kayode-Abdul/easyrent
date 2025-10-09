<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookingApiController extends Controller
{
    public function index()
    {
        $bookings = DB::table('bookings')->orderByDesc('created_at')->limit(50)->get();
        return response()->json($bookings);
    }

    public function store(Request $request)
    {
        $data = $request->only(['property_id','tenant_id','status','scheduled_date','notes']);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $id = DB::table('bookings')->insertGetId($data);
        return response()->json(['id' => $id], 201);
    }

    public function show($id)
    {
        $booking = DB::table('bookings')->where('id', $id)->first();
        abort_unless($booking, 404);
        return response()->json($booking);
    }

    public function update(Request $request, $id)
    {
        $data = $request->only(['status','scheduled_date','notes']);
        $data['updated_at'] = now();
        $updated = DB::table('bookings')->where('id', $id)->update($data);
        abort_unless($updated, 404);
        $booking = DB::table('bookings')->where('id', $id)->first();
        return response()->json($booking);
    }
}
