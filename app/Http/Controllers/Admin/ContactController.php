<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactMessage;
use App\Services\ContactService;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function __construct(
        private ContactService $contactService
    ) {}

    public function index(Request $request)
    {
        $messages = ContactMessage::with('handler:id,name')
            ->when($request->status, fn ($q, $s) => $q->where('status', $s))
            ->when($request->search, function ($q, $s) {
                $q->where(function ($query) use ($s) {
                    $query->where('name', 'LIKE', "%{$s}%")
                        ->orWhere('subject', 'LIKE', "%{$s}%")
                        ->orWhere('email', 'LIKE', "%{$s}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.contacts.index', compact('messages'));
    }

    public function show(ContactMessage $message)
    {
        $message->load('handler');

        if ($message->status === 'new') {
            $this->contactService->updateStatus($message, 'read', request()->user());
        }

        return view('admin.contacts.show', compact('message'));
    }

    public function updateStatus(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'status' => 'required|in:new,read,in_progress,resolved,archived',
            'admin_note' => 'nullable|string',
        ]);

        if (isset($data['admin_note'])) {
            $message->update(['admin_note' => $data['admin_note']]);
        }

        $this->contactService->updateStatus($message, $data['status'], $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Status pesan berhasil diupdate.',
        ]);
    }

    /**
     * Balasan yang akan dilihat pengunjung di halaman cek status tiket.
     */
    public function reply(Request $request, ContactMessage $message)
    {
        $data = $request->validate([
            'reply' => 'required|string|max:5000',
        ]);

        $this->contactService->reply($message, $data['reply'], $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Balasan berhasil dikirim ke pengunjung.',
        ]);
    }

    public function destroy(Request $request, ContactMessage $message)
    {
        $this->contactService->delete($message, $request->user());

        return response()->json([
            'success' => true,
            'message' => 'Pesan berhasil dihapus.',
        ]);
    }
}
