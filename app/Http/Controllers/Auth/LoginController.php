<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                return back()->with('error', 'Your account has been deactivated. Please contact clinic management.');
            }

            return $this->redirectBasedOnRole($user)
                ->with('success', "Welcome back, {$user->name}!");
        }

        return back()->withInput($request->only('email'))
            ->with('error', 'Invalid email or password provided.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'You have been successfully logged out.');
    }

    protected function redirectBasedOnRole($user)
    {
        return match ($user->role) {
            'cashier' => redirect()->route('cashier.dashboard'),
            'veterinarian' => redirect()->route('vet.dashboard'),
            'manager' => redirect()->route('manager.dashboard'),
            default => redirect()->route('admin.dashboard'),
        };
    }
}
