<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $login = $credentials['login'];
        $isEmail = filter_var($login, FILTER_VALIDATE_EMAIL) !== false;
        $field = $isEmail ? 'email' : 'username';

        $auth = Auth::attempt([$field => $login, 'password' => $credentials['password']]);

        if ($auth) {
            $request->session()->regenerate();

            $user = Auth::user();
            $user->update(['last_login_at' => now()]);

            ActivityLog::create([
                'user_id' => $user->id,
                'module' => 'Auth',
                'action' => 'LOGIN',
                'description' => $user->name . ' login ke sistem',
                'ip_hash' => hash('sha256', $request->ip() ?? ''),
                'user_agent' => substr($request->userAgent() ?? '', 0, 500),
                'created_at' => now(),
            ]);

            return redirect()->intended($this->redirectPath());
        }

        return back()
            ->withInput($request->only('login'))
            ->withErrors(['login' => 'Email/username atau password salah.']);
    }

    public function logout(Request $request)
    {
        $user = $request->user();

        ActivityLog::create([
            'user_id' => $user?->id,
            'module' => 'Auth',
            'action' => 'LOGOUT',
            'description' => ($user?->name ?? 'User') . ' logout dari sistem',
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    private function redirectPath(): string
    {
        $user = Auth::user();

        return match (true) {
            $user?->isAdmin() => route('admin.dashboard'),
            $user?->isReporter() => route('reporter.dashboard'),
            $user?->isRedaktur() => route('redaktur.dashboard'),
            default => route('login'),
        };
    }
}
