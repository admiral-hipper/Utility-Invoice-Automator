<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);
        $user = User::where('email', $data['email'])->first();
        if (! $user || !Hash::check($data['password'], $user->password)) {
            throw new Exception('Incorrect email or password', 403);
        }

        $abilities = $user->isAdmin()
            ? ['admin']
            : ['customer'];
        $token = $user->createToken('api', $abilities)->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'token_type' => 'Bearer',
            ],
        ]);
    }

    public function me(Request $request): JsonResponse
    {
        return response()->json([
            'id' => $request->user()->id,
            'email' => $request->user()->email,
            'name' => $request->user()->name,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        // удалить текущий токен
        $request->user()->currentAccessToken()?->delete();

        return response()->json(['status' => 'successful', 'code' => 200]);
    }
}
