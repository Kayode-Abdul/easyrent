<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use App\Models\User;
use App\Models\Property;

class UserApiController extends Controller
{
    public function show($id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $data = Arr::only($user->toArray(), [
            'user_id','first_name','last_name','email','phone','state','lga','created_at','updated_at'
        ]);
        return response()->json($data);
    }

    public function properties($id)
    {
        $properties = Property::where('user_id', $id)
            ->orderByDesc('created_at')
            ->limit(50)
            ->get([
                'prop_id','title','address','state','lga','status','created_at','updated_at'
            ]);
        return response()->json($properties);
    }

    public function update(Request $request, $id)
    {
        $user = User::where('user_id', $id)->firstOrFail();
        $data = $request->only(['first_name','last_name','phone','state','lga']);
        if (!empty($data)) {
            $user->fill($data);
            $user->save();
        }
        $resp = Arr::only($user->fresh()->toArray(), [
            'user_id','first_name','last_name','email','phone','state','lga','created_at','updated_at'
        ]);
        return response()->json($resp);
    }
}
