<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;


use App\Models\User;

class LoginController extends Controller
{
    //

    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username_or_email' => ['required'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $credentials['username_or_email'])
            ->orWhere('username', $credentials['username_or_email'])
            ->first();


        // Check if user exists and password is correct
        if ($user && Hash::check($credentials['password'], $user->password)) {
            // Log the user in
            Auth::login($user);
            $request->session()->regenerate();
            return redirect()->intended('dashboard');
        } else {
            // Authentication failed
            return back()->withErrors(['username_or_email' => 'Invalid credentials.']);
        }

    }


    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
