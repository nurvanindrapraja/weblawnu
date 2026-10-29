<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\ContactMessage;
use App\Models\User;

class ContactService
{
    public function store(array $data): ContactMessage
    {
        return ContactMessage::create([
            'ticket_number' => $this->generateTicketNumber(),
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
            'status' => 'new',
        ]);
    }

    /**
     * Nomor tiket untuk pengunjung, contoh: TKT-8FK2QZ.
     * Karakter ambigu (O/0, I/1) dihindari agar mudah diketik ulang.
     */
    public function generateTicketNumber(): string
    {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';

        do {
            $ticket = 'TKT-';

            for ($i = 0; $i < 6; $i++) {
                $ticket .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (ContactMessage::query()->where('ticket_number', $ticket)->exists());

        return $ticket;
    }

    /**
     * Balasan untuk pengunjung (tampil di halaman cek status tiket).
     */
    public function reply(ContactMessage $message, string $reply, ?User $handler = null): ContactMessage
    {
        $message->update([
            'reply' => $reply,
            'replied_at' => now(),
        ]);

        // Pesan yang baru masuk (belum diproses manual) naik ke
        // "diproses" saat dibalas.
        if (in_array($message->status, ['new', 'read'])) {
            $this->updateStatus($message, 'in_progress', $handler);
        }

        return $message->refresh();
    }

    public function updateStatus(ContactMessage $message, string $status, ?User $handler = null): ContactMessage
    {
        $message->update([
            'status' => $status,
            'handled_by' => $handler?->id,
            'handled_at' => now(),
        ]);

        return $message->refresh();
    }

    public function delete(ContactMessage $message, User $user): bool
    {
        $message->delete();

        ActivityLog::create([
            'user_id' => $user->id,
            'module' => 'Contact',
            'action' => 'DELETE',
            'record_id' => $message->id,
            'description' => "Pesan kontak '{$message->subject}' dihapus",
            'ip_hash' => hash('sha256', request()->ip() ?? ''),
            'user_agent' => substr(request()->userAgent() ?? '', 0, 500),
            'created_at' => now(),
        ]);

        return true;
    }
}
