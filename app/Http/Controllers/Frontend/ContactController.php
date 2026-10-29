<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    public function index()
    {
        return view('frontend.contact');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:30',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|min:10',
        ]);

        // Email or phone must be provided
        if (empty($data['email']) && empty($data['phone'])) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau nomor HP minimal salah satunya harus diisi.',
            ], 422);
        }

        $message = $this->contactService->store($data);

        return response()->json([
            'success' => true,
            'message' => 'Terima kasih. Pesan Anda telah berhasil dikirim dan akan segera ditindaklanjuti.',
            'ticket_number' => $message->ticket_number,
        ]);
    }

    /**
     * Halaman publik cek status pesan berdasarkan nomor tiket.
     */
    public function status(Request $request)
    {
        $validated = $request->validate([
            'ticket' => 'nullable|string|max:20',
        ]);

        $ticket = strtoupper(trim((string) ($validated['ticket'] ?? '')));

        $message = $ticket !== ''
            ? ContactMessage::where('ticket_number', $ticket)->first()
            : null;

        return view('frontend.contact-status', compact('message', 'ticket'));
    }
}
