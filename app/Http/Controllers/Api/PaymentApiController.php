<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentApiController extends Controller
{
    public function index()
    {
        $payments = DB::table('payments')->orderByDesc('created_at')->limit(50)->get();
        return response()->json($payments);
    }

    public function show($id)
    {
        $payment = DB::table('payments')->where('id', $id)->first();
        abort_unless($payment, 404);
        return response()->json($payment);
    }

    public function store(Request $request)
    {
        $data = $request->only(['booking_id','tenant_id','amount','status','reference']);
        $data['created_at'] = now();
        $data['updated_at'] = now();
        $id = DB::table('payments')->insertGetId($data);
        return response()->json(['id' => $id], 201);
    }
}
