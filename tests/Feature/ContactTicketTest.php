<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactTicketTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_submission_returns_ticket_number(): void
    {
        $response = $this->postJson('/api/frontend/contact', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'subject' => 'Pertanyaan Wakaf',
            'message' => 'Saya ingin bertanya tentang prosedur wakaf tanah.',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('ticket_number', fn ($ticket) => preg_match('/^TKT-[23456789A-HJ-KM-NP-Z]{6}$/', $ticket) === 1);

        $this->assertDatabaseCount('contact_messages', 1);
        $this->assertNotNull(ContactMessage::first()->ticket_number);
    }

    public function test_visitor_can_check_status_with_valid_ticket(): void
    {
        $message = ContactMessage::factory()->create([
            'ticket_number' => 'TKT-AB12CD',
            'subject' => 'Tanya prosedur wakaf',
            'status' => 'new',
        ]);

        $this->get(route('contact.status', ['ticket' => $message->ticket_number]))
            ->assertOk()
            ->assertSee('TKT-AB12CD')
            ->assertSee('Tanya prosedur wakaf')
            ->assertSee('Baru Diterima')
            ->assertSee('Belum ada balasan');
    }

    public function test_ticket_lookup_is_case_insensitive(): void
    {
        $message = ContactMessage::factory()->create(['ticket_number' => 'TKT-AB12CD']);

        $this->get(route('contact.status', ['ticket' => 'tkt-ab12cd']))
            ->assertOk()
            ->assertSee('TKT-AB12CD');
    }

    public function test_visitor_status_check_with_unknown_ticket_shows_not_found(): void
    {
        $this->get(route('contact.status', ['ticket' => 'TKT-ZZZZZZ']))
            ->assertOk()
            ->assertSee('Tiket tidak ditemukan');
    }

    public function test_admin_reply_is_visible_to_visitor_and_moves_status(): void
    {
        $admin = User::factory()->admin()->create();
        // "read" = kondisi realistis: admin sudah membuka pesan (show
        // menandai new → read) sebelum membalas.
        $message = ContactMessage::factory()->create(['status' => 'read']);

        $this->actingAs($admin)
            ->patchJson(route('admin.contacts.reply', $message->id), [
                'reply' => 'Terima kasih, pesan Anda telah kami terima.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Terima kasih, pesan Anda telah kami terima.', $message->fresh()->reply);
        $this->assertSame('in_progress', $message->fresh()->status);
        $this->assertNotNull($message->fresh()->replied_at);

        $this->get(route('contact.status', ['ticket' => $message->ticket_number]))
            ->assertOk()
            ->assertSee('Terima kasih, pesan Anda telah kami terima.')
            ->assertSee('Sedang Diproses');
    }

    public function test_reply_does_not_override_manual_status(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create(['status' => 'resolved']);

        $this->actingAs($admin)
            ->patchJson(route('admin.contacts.reply', $message->id), ['reply' => 'Saran terakhir.'])
            ->assertOk();

        $this->assertSame('resolved', $message->fresh()->status);
    }

    public function test_redaktur_can_reply_to_visitor(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $message = ContactMessage::factory()->create(['status' => 'new']);

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.contacts.reply', $message->id), [
                'reply' => 'Sedang kami telusuri.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('Sedang kami telusuri.', $message->fresh()->reply);
    }

    public function test_reply_requires_text(): void
    {
        $admin = User::factory()->admin()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($admin)
            ->patchJson(route('admin.contacts.reply', $message->id), ['reply' => ''])
            ->assertStatus(422);
    }

    public function test_admin_contact_index_shows_ticket_number(): void
    {
        $admin = User::factory()->admin()->create();
        ContactMessage::factory()->create(['ticket_number' => 'TKT-QR56WX']);

        $this->actingAs($admin)
            ->get(route('admin.contacts.index'))
            ->assertOk()
            ->assertSee('TKT-QR56WX');
    }
}
