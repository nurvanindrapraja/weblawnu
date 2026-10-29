<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Registration;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class RegistrationController extends Controller
{
    public function index(Request $request)
    {
        $registrations = Registration::with('verifier:id,name')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where(function ($query) use ($s) {
                    $query->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('email', 'LIKE', "%{$s}%")
                        ->orWhere('username', 'LIKE', "%{$s}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        $counts = [
            'all' => Registration::count(),
            'pending' => Registration::where('status', Registration::STATUS_PENDING)->count(),
            'approved' => Registration::where('status', Registration::STATUS_APPROVED)->count(),
            'rejected' => Registration::where('status', Registration::STATUS_REJECTED)->count(),
        ];

        return view('admin.registrations.index', compact('registrations', 'counts'));
    }

    public function approve(Request $request, Registration $registration)
    {
        if (! $registration->isPending()) {
            return $this->rejectedResponse('Pendaftaran sudah diproses sebelumnya.');
        }

        // Cegah duplikasi jika akun dibuat lewat jalur lain sembari pending.
        $userExists = User::where('email', $registration->email)
            ->orWhere('username', $registration->username)
            ->exists();

        if ($userExists) {
            return $this->rejectedResponse("Email atau username '{$registration->email}' sudah terdaftar sebagai pengguna.");
        }

        $user = User::create([
            'name' => $registration->name,
            'username' => $registration->username,
            'email' => $registration->email,
            'phone' => $registration->phone,
            'password' => $registration->password, // sudah di-hash saat mendaftar
            'status' => 'active',
        ]);

        $role = Role::where('name', $registration->role)->first();
        if (! $role) {
            return $this->rejectedResponse("Role '{$registration->role}' tidak ditemukan di sistem.");
        }

        $user->roles()->sync([$role->id]);

        $registration->update([
            'status' => Registration::STATUS_APPROVED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'admin_note' => $request->admin_note,
        ]);

        $this->logActivity($request, "Pendaftaran '{$registration->name}' ({$registration->role}) disetujui — user ID {$user->id} dibuat");

        return $this->okResponse("Pendaftaran disetujui. Akun '{$registration->username}' ({$registration->role}) berhasil dibuat.");
    }

    public function reject(Request $request, Registration $registration)
    {
        $data = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        if (! $registration->isPending()) {
            return $this->rejectedResponse('Pendaftaran sudah diproses sebelumnya.');
        }

        $registration->update([
            'status' => Registration::STATUS_REJECTED,
            'verified_by' => $request->user()->id,
            'verified_at' => now(),
            'admin_note' => $data['admin_note'],
        ]);

        $this->logActivity($request, "Pendaftaran '{$registration->name}' ({$registration->role}) ditolak");

        return $this->okResponse('Pendaftaran ditolak.');
    }

    private function okResponse(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['success' => true, 'message' => $message]);
        }

        return redirect()->route('admin.registrations.index')->with('success', $message);
    }

    private function rejectedResponse(string $message)
    {
        if (request()->expectsJson()) {
            return response()->json(['success' => false, 'message' => $message], 400);
        }

        return redirect()->back()->with('error', $message);
    }

    private function logActivity(Request $request, string $description): void
    {
        ActivityLog::create([
            'user_id' => $request->user()->id,
            'module' => 'Registrations',
            'action' => 'VERIFY',
            'record_id' => null,
            'description' => $description,
            'ip_hash' => hash('sha256', $request->ip() ?? ''),
            'user_agent' => substr($request->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);
    }
}
