<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:users,email|unique:registrations,email',
            'username' => 'required|string|min:3|max:50|alpha_dash|unique:users,username|unique:registrations,username',
            'phone' => 'nullable|string|max:30',
            'role' => 'required|in:'.implode(',', Registration::ALLOWED_ROLES),
            'reason' => 'nullable|string|max:1000',
            'password' => 'required|string|min:8|confirmed',
        ], [], [
            'username' => 'nama pengguna',
            'role' => 'peran',
            'reason' => 'alasan/motivasi',
        ]);

        Registration::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'status' => Registration::STATUS_PENDING,
        ]);

        return redirect()
            ->route('register.success')
            ->with('success', 'Pendaftaran Anda telah kami terima dan akan diverifikasi oleh admin. Anda akan dapat login setelah akun disetujui.');
    }

    public function success()
    {
        return view('auth.register-success');
    }
}
