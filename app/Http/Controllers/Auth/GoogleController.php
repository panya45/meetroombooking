<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Exception;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleCallback()
    {
        try {
            // Get the user from Google
            $googleUser = Socialite::driver('google')->user();

            // Find the user in the database by email
            $user = User::where('email', $googleUser->getEmail())->first();

            if (!$user) {
                // Create a new user if they don't exist
                $user = User::create([
                    'username' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'password' => bcrypt(Str::random(16)), // Create a random password if no password exists
                ]);
            }

            // Check if the Google ID matches the one in the database
            if ($user->google_id !== $googleUser->getId()) {
                $user->update(['google_id' => $googleUser->getId()]);
            }

            // Log the user in
            Auth::login($user);

            return redirect()->route('home')->with('success', 'Logged in successfully with Google!');
        } catch (Exception $e) {
            return redirect('/')->with('error', 'Error logging in with Google: ' . $e->getMessage());
        }
    }
}
