<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'first_name' => 'sometimes|required|string|max:255',
            'last_name' => 'sometimes|required|string|max:255',
            'phone_number' => 'sometimes|required|string|max:255',
            'pin' => 'sometimes|required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors(),
            ], 422);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name' => $request->last_name,
            'phone_number' => $request->phone_number,
            'pin' => $request->pin,
            'address' => $request->address
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'user_id' => $user->uuid,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'phone_number' => $user->phone_number,
                'address' => $user->address,
                'created_date' => $user->created_at,
            ]
        ], Response::HTTP_OK);
    }

    public function login(Request $request)
    {
        $credentials = $request->only('phone_number', 'pin');
        $user = User::where('phone_number', $credentials['phone_number'])->first();

        if (!$user || $user->pin !== $credentials['pin']) {
            return response()->json(['message' => 'Phone Number and PIN doesn’t match'], 401);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'status' => 'SUCCESS',
            'result' => [
                'access_token' => $token,
                'refresh_token' => $token,
            ]
        ]);
    }
}
