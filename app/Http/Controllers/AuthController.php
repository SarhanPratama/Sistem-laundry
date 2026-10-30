<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('username', $request->username)->first();

        if (!$user) {
            return back()->withErrors(['credentials' => 'Username atau password tidak valid.']);
        }

        if (!$user->is_active) {
            return back()->withErrors(['credentials' => 'Akun Anda telah dinonaktifkan.']);
        }

        if ($user->locked_until && $user->locked_until->isFuture()) {
            return back()->withErrors([
                'credentials' => 'Akun Anda terkunci hingga ' . $user->locked_until->format('d/m/Y H:i') . '. Silakan coba lagi nanti.',
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            $user->failed_login_count++;

            if ($user->failed_login_count >= 5) {
                $user->locked_until = now()->addMinutes(15);
                $user->failed_login_count = 0;
            }

            $user->save();

            return back()->withErrors(['credentials' => 'Kredensial tidak valid.']);
        }

        $user->failed_login_count = 0;
        $user->locked_until = null;
        $user->last_login = now();
        $user->save();

        Auth::login($user);
        $request->session()->regenerate();

        ActivityLog::create([
            'user_id' => $user->id,
            'action' => 'login',
            'entity_type' => 'User',
            'entity_id' => $user->id,
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
