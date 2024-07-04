<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;

        if ($request->has('address')) {
            $user->address = $request->address;
        }

        $user->save();

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'user_id' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'address' => $user->address,
                'updated_at' => $user->updated_at,
            ]
        ]);
    }
}
