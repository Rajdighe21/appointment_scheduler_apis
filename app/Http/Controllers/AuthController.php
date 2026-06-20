<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;


class AuthController extends Controller
{
    public function register(Request $request)
    {

        $validate  = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|unique:users,email',
            'role' => 'required',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // dd($validate);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->password = Hash::make($request->password);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'User Register Successfully',
            'user' => $user
        ], 201);
    }



    public function login(Request $request)
    {
        $validate = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $validate['email'])->first();

        if (!$user || !Hash::check($validate['password'], $user->password)) {

            return response()->json([
                'status' => false,
                'message' => 'Invalid credentials'
            ], 401);
        }

        $plainToken = Str::random(80);

        $user->api_token = hash('sha256', $plainToken);
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Login Successful',
            'token' => $plainToken,
            'user' => $user
        ]);
    }

    public function profile()
    {
        return response()->json([
            'status' => true,
            'user' => auth()->user()
        ]);
    }
}
