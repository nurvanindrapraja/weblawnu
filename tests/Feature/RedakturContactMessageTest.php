<?php

namespace Tests\Feature;

use App\Models\ContactMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedakturContactMessageTest extends TestCase
{
    use RefreshDatabase;

    public function test_redaktur_can_view_contact_messages(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $message = ContactMessage::factory()->create(['subject' => 'Tanya soal wakaf tanah']);

        $this->actingAs($redaktur)
            ->get(route('redaktur.contacts.index'))
            ->assertOk()
            ->assertSee('Tanya soal wakaf tanah')
            // Redaktur tidak punya tombol hapus.
            ->assertDontSee('onclick="deleteMsg(', false);
    }

    public function test_redaktur_can_view_contact_message_detail(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $message = ContactMessage::factory()->create(['subject' => 'Detail wakaf']);

        $this->actingAs($redaktur)
            ->get(route('redaktur.contacts.show', $message->id))
            ->assertOk()
            ->assertSee('Detail wakaf');
    }

    public function test_redaktur_can_update_contact_message_status(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.contacts.update-status', $message->id), [
                'status' => 'in_progress',
                'admin_note' => 'Sedang ditindaklanjuti.',
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('in_progress', $message->fresh()->status);
    }

    public function test_redaktur_cannot_delete_contact_message(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $message = ContactMessage::factory()->create();

        $this->actingAs($redaktur)
            ->delete(route('admin.contacts.destroy', $message->id))
            ->assertForbidden();

        $this->assertDatabaseHas('contact_messages', ['id' => $message->id]);
    }

    public function test_admin_contact_index_still_shows_delete_button(): void
    {
        $admin = User::factory()->admin()->create();
        ContactMessage::factory()->create();

        $this->actingAs($admin)
            ->get(route('admin.contacts.index'))
            ->assertOk()
            ->assertSee('onclick="deleteMsg(', false);
    }
}
