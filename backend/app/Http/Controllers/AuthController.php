<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;

class AuthController extends Controller
{

    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function createUser(LoginRequest $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User Created Successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);

    }

    public function login(LoginRequest $request)
    {
        $check = Auth::guard()->attempt(['email' => $request->email, 'password' => $request->password]);
        if($check) {
            $user = User::where('email', $request->email)->first();
            return $this->createUserToken($user);
        }

        abort(401, 'Login failed');
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
    }

    private function createUserToken(User $user)
    {
        $token = $user->createToken('token');
        return ['token' => $token->plainTextToken];
    }

}
