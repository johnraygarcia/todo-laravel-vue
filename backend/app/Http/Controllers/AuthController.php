<?php

namespace App\Http\Controllers;

use App\Mail\WelcomeUserEmail;
use App\Models\User;
use App\Notifications\WelcomeUserNotification;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Validator;

class AuthController extends Controller
{

    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function createUser(Request $request)
    {
        try {
            //Validated
            $validateUser = Validator::make($request->all(),
            [
                'name' => 'required',
                'email' => 'required|email|unique:users,email',
                'password' => 'required|confirmed|min:7|max:255'
            ]);

            if($validateUser->fails()){
                return response()->json([
                    'status' => false,
                    'message' => 'validation error',
                    'errors' => $validateUser->errors()
                ], 401);
            }

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

        } catch (\Throwable $th) {
            return response()->json([
                'status' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        Validator::make($request->all(),
        [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required'
        ]);

        if (!Auth::validate($request->all()))
        {
            return response()->json(['message' => 'User not found.'], 401);
        }

        $user = User::where('email', $request->email)->first();

        return $this->createUserToken($user);
    }

    private function createUserToken(User $user)
    {
        $token = $user->createToken('token');
        return ['token' => $token->plainTextToken];
    }

}
