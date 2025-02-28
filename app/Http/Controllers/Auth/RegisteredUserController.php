<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class RegisteredUserController extends Controller
{
    public function showRegistrationForm()
    {
        return view('auth.register');

    }
    protected function redirectTo()
    {
        return '/rooms';
    }

    public function Register(Request $request)
    {
        $fields = $request->validate([
            'username' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|confirmed'
        ]);

        $user = User::create([
            'username' => $fields['username'],
            'email' => $fields['email'],
            'password' => Hash::make($fields['password']),
        ]);

        $token = $user->createToken($user->username);

        session(['user_token' => $token->plainTextToken]);
        return redirect('/rooms');
    }

    public function Login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return ['message' => 'The credentials are incorrect.'];
        }

        $token = $user->createToken($user->username);
        return ['user' => $user, 'token' => $token->plainTextToken];
    }

    public function Logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return ['message' => 'You are logged out.'];
    }
}
