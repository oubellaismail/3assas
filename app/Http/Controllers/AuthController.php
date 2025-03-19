<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required|min:6',
        ]);

        // Attempt login
        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Redirect to a new view with user details
            return view('dashboard', ['user' => $user]);
        }

        // Redirect back with an error message if login fails
        return back()->withErrors(['login' => 'Invalid email or password']);
    }

    public function getLogin(Request $req){
        return view('login');

    }

    public function logout(Request $req){
        Auth::logout();
        return redirect('/login');
    }

    public function showForm()
    {
        return view('register'); // Show the registration form
    }

    public function register(Request $request)
    {
        // Validate input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users|max:255',
            'password' => 'required|min:6|confirmed',
        ]);

        // Create user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return redirect('/dashboard')->with([
            'success' => 'Registration successful!',
            'user' => $user
        ]);
    }
}
