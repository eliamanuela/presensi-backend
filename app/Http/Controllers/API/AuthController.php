<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use \Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8'
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors());
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()
            ->json(['data' => $user, 'access_token' => $token, 'token_type' => 'Bearer',]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()
                ->json(['message' => 'Unauthorized'], 401);
        }

        $user = User::where('email', $request['email'])->firstOrFail();

        $token = $user->createToken('auth_token')->plainTextToken;
        $user->token = $token;
        $user->token_type = 'Bearer';

        return response()
            ->json([
                'success' => true,
                'message' => 'Hi ' . $user->name . ', selamat datang di sistem presensi',
                'data' => $user
            ]);
    }

    // method for user logout and delete token
    public function logout()
    {
        $user = auth()->user();

        // Revoke the user's tokens
        $user->tokens->each(function ($token, $key) {
            $token->delete();
        });

        // Additional actions, if needed
        // For example, you can log the user out of the current session.

        // Return a response
        return response()->json([
            'message' => 'You have successfully logged out, and all tokens were revoked',
            'user' => $user,
            'additional_data' => 'You can include any additional information here'
        ]);
    }
}
