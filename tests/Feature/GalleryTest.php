<?php

namespace Tests\Feature;

use App\Models\Gallery;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryTest extends TestCase
{
    use RefreshDatabase;

    private function attachImages(Gallery $gallery, int $count): void
    {
        foreach (range(1, $count) as $i) {
            $media = Media::factory()->create(['alt_text' => "Foto {$i}"]);
            $gallery->images()->attach($media->id, ['caption' => "Keterangan foto ke-{$i}", 'sort_order' => $i]);
        }
    }

    public function test_public_gallery_index_shows_only_published_galleries(): void
    {
        $published = Gallery::factory()->published()->create(['title' => 'Bakti Sosial 2026']);
        $this->attachImages($published, 2);
        Gallery::factory()->create(['title' => 'Kegiatan Draft Tersembunyi']);
        Gallery::factory()->submitted()->create(['title' => 'Kegiatan Menunggu Review']);

        $this->get('/galeri')
            ->assertOk()
            ->assertSee('Bakti Sosial 2026')
            ->assertDontSee('Kegiatan Draft Tersembunyi')
            ->assertDontSee('Kegiatan Menunggu Review');
    }

    public function test_public_gallery_detail_shows_all_images_with_captions(): void
    {
        $gallery = Gallery::factory()->published()->create(['title' => 'Haul KH Hasyim Asyari']);
        $this->attachImages($gallery, 3);

        $this->get('/galeri/'.$gallery->slug)
            ->assertOk()
            ->assertSee('Haul KH Hasyim Asyari')
            ->assertSee('Keterangan foto ke-1')
            ->assertSee('Keterangan foto ke-2')
            ->assertSee('Keterangan foto ke-3');
    }

    public function test_public_gallery_detail_returns_404_for_unpublished(): void
    {
        $draft = Gallery::factory()->create();
        $submitted = Gallery::factory()->submitted()->create();
        $rejected = Gallery::factory()->rejected()->create();

        $this->get('/galeri/'.$draft->slug)->assertNotFound();
        $this->get('/galeri/'.$submitted->slug)->assertNotFound();
        $this->get('/galeri/'.$rejected->slug)->assertNotFound();
    }

    public function test_reporter_can_create_gallery_with_multiple_images_and_captions(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->reporter()->create();

        $response = $this->actingAs($reporter)->postJson(route('reporter.galleries.store'), [
            'title' => 'Penanaman Pohon di Ponpes',
            'event_date' => '2026-05-01',
            'description' => 'Kegiatan penghijauan bersama santri.',
            'images' => [
                UploadedFile::fake()->image('foto1.jpg'),
                UploadedFile::fake()->image('foto2.jpg'),
                UploadedFile::fake()->image('foto3.jpg'),
            ],
            'image_captions' => [
                0 => 'Pembukaan kegiatan',
                1 => 'Proses penanaman',
            ],
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $gallery = Gallery::where('title', 'Penanaman Pohon di Ponpes')->first();
        $this->assertNotNull($gallery);
        $this->assertSame('draft', $gallery->status);
        $this->assertSame($reporter->id, $gallery->author_id);
        $this->assertCount(3, $gallery->images);

        $captions = $gallery->images->pluck('pivot.caption')->all();
        $this->assertContains('Pembukaan kegiatan', $captions);
        $this->assertContains('Proses penanaman', $captions);
        $this->assertContains('', $captions); // foto tanpa keterangan tetap tersimpan
    }

    public function test_reporter_gallery_is_not_public_before_approval(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)->postJson(route('reporter.galleries.store'), [
            'title' => 'Kegiatan Belum Diverifikasi',
            'images' => [UploadedFile::fake()->image('a.jpg')],
        ])->assertCreated();

        $gallery = Gallery::where('title', 'Kegiatan Belum Diverifikasi')->first();

        $this->get('/galeri/'.$gallery->slug)->assertNotFound();
        $this->get('/galeri')->assertDontSee('Kegiatan Belum Diverifikasi');
    }

    public function test_reporter_cannot_edit_others_gallery(): void
    {
        $owner = User::factory()->reporter()->create();
        $other = User::factory()->reporter()->create();
        $gallery = Gallery::factory()->for($owner, 'author')->create();

        $this->actingAs($other)
            ->get(route('reporter.galleries.edit', $gallery))
            ->assertForbidden();
    }

    public function test_reporter_cannot_edit_published_gallery(): void
    {
        $reporter = User::factory()->reporter()->create();
        $gallery = Gallery::factory()->for($reporter, 'author')->published()->create();

        $this->actingAs($reporter)
            ->get(route('reporter.galleries.edit', $gallery))
            ->assertForbidden();

        $this->actingAs($reporter)
            ->putJson(route('reporter.galleries.update', $gallery), ['title' => 'Ubah Judul'])
            ->assertStatus(403);
    }

    public function test_reporter_submit_requires_at_least_one_image(): void
    {
        $reporter = User::factory()->reporter()->create();
        $gallery = Gallery::factory()->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->patchJson(route('reporter.galleries.submit', $gallery))
            ->assertStatus(400)
            ->assertJsonPath('success', false);

        $this->assertSame('draft', $gallery->fresh()->status);
    }

    public function test_reporter_submit_then_redaktur_approve_publishes_gallery(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->reporter()->create();
        $redaktur = User::factory()->redaktur()->create();

        $this->actingAs($reporter)->postJson(route('reporter.galleries.store'), [
            'title' => 'Kenduri Fatik',
            'images' => [UploadedFile::fake()->image('kenduri.jpg')],
            'image_captions' => [0 => 'Suasana kenduri'],
        ])->assertCreated();

        $gallery = Gallery::where('title', 'Kenduri Fatik')->first();

        // Submit untuk review
        $this->actingAs($reporter)
            ->patchJson(route('reporter.galleries.submit', $gallery))
            ->assertOk()
            ->assertJsonPath('success', true);
        $this->assertSame('submitted', $gallery->fresh()->status);

        // Masih tersembunyi di publik
        $this->get('/galeri/'.$gallery->slug)->assertNotFound();

        // Redaktur menyetujui
        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.galleries.approve', $gallery))
            ->assertOk()
            ->assertJsonPath('success', true);

        $fresh = $gallery->fresh();
        $this->assertSame('published', $fresh->status);
        $this->assertNotNull($fresh->published_at);
        $this->assertSame($redaktur->id, $fresh->reviewed_by);

        // Sekarang tampil di publik lengkap dengan keterangannya
        $this->get('/galeri/'.$gallery->slug)
            ->assertOk()
            ->assertSee('Suasana kenduri');
    }

    public function test_redaktur_cannot_approve_twice(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $gallery = Gallery::factory()->submitted()->create();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.galleries.approve', $gallery))
            ->assertOk();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.galleries.approve', $gallery))
            ->assertStatus(400);
    }

    public function test_redaktur_reject_requires_note_and_sets_rejected(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $gallery = Gallery::factory()->submitted()->create();

        // Tanpa catatan → 422
        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.galleries.reject', $gallery), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('note');
        $this->assertSame('submitted', $gallery->fresh()->status);

        // Dengan catatan → rejected
        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.galleries.reject', $gallery), ['note' => 'Foto terlalu buram.'])
            ->assertOk()
            ->assertJsonPath('success', true);

        $fresh = $gallery->fresh();
        $this->assertSame('rejected', $fresh->status);
        $this->assertSame('Foto terlalu buram.', $fresh->rejection_note);
        $this->get('/galeri/'.$fresh->slug)->assertNotFound();
    }

    public function test_reporter_can_edit_and_resubmit_rejected_gallery(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->reporter()->create();
        $gallery = Gallery::factory()->for($reporter, 'author')->rejected('Foto buram.')->create();

        $this->actingAs($reporter)
            ->putJson(route('reporter.galleries.update', $gallery), [
                'title' => $gallery->title.' (revisi)',
                'images' => [UploadedFile::fake()->image('revisi.jpg')],
            ])
            ->assertOk()
            ->assertJsonPath('success', true);

        $fresh = $gallery->fresh();
        $this->assertSame('draft', $fresh->status);
        $this->assertNull($fresh->rejection_note);
        $this->assertNotNull($fresh->images->first());

        $this->actingAs($reporter)
            ->patchJson(route('reporter.galleries.submit', $fresh))
            ->assertOk();
        $this->assertSame('submitted', $fresh->fresh()->status);
    }

    public function test_reporter_cannot_delete_non_draft_gallery(): void
    {
        $reporter = User::factory()->reporter()->create();
        $submitted = Gallery::factory()->for($reporter, 'author')->submitted()->create();

        $this->actingAs($reporter)
            ->deleteJson(route('reporter.galleries.destroy', $submitted))
            ->assertStatus(403);
        $this->assertNotNull($submitted->fresh());
    }

    public function test_admin_can_create_and_publish_gallery_directly(): void
    {
        Storage::fake('public');
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->postJson(route('admin.galleries.store'), [
            'title' => 'Kegiatan Internal Admin',
            'images' => [UploadedFile::fake()->image('admin.jpg')],
        ])->assertCreated();

        $gallery = Gallery::where('title', 'Kegiatan Internal Admin')->first();
        $this->assertSame('draft', $gallery->status);

        $this->actingAs($admin)
            ->patchJson(route('admin.galleries.publish', $gallery))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSame('published', $gallery->fresh()->status);
        $this->get('/galeri/'.$gallery->slug)->assertOk();
    }

    public function test_admin_gallery_index_renders(): void
    {
        $admin = User::factory()->admin()->create();
        $gallery = Gallery::factory()->create(['title' => 'Galeri Uji Admin']);
        $this->attachImages($gallery, 1);

        $this->actingAs($admin)
            ->get(route('admin.galleries.index'))
            ->assertOk()
            ->assertSee('Galeri Uji Admin');
    }

    public function test_reporter_gallery_index_only_shows_own_galleries(): void
    {
        $reporter = User::factory()->reporter()->create();
        $other = User::factory()->reporter()->create();
        Gallery::factory()->for($reporter, 'author')->create(['title' => 'Galeri Milikku']);
        Gallery::factory()->for($other, 'author')->create(['title' => 'Galeri Orang Lain']);

        $this->actingAs($reporter)
            ->get(route('reporter.galleries.index'))
            ->assertOk()
            ->assertSee('Galeri Milikku')
            ->assertDontSee('Galeri Orang Lain');
    }

    public function test_permissions_between_roles(): void
    {
        $reporter = User::factory()->reporter()->create();
        $redaktur = User::factory()->redaktur()->create();

        // Reporter tidak bisa masuk panel admin galeri
        $this->actingAs($reporter)
            ->get(route('admin.galleries.index'))
            ->assertForbidden();

        // Reporter tidak bisa review galeri
        $gallery = Gallery::factory()->submitted()->create();
        $this->actingAs($reporter)
            ->patchJson(route('redaktur.galleries.approve', $gallery))
            ->assertForbidden();

        // Redaktur tidak bisa CRUD galeri admin
        $this->actingAs($redaktur)
            ->get(route('admin.galleries.index'))
            ->assertForbidden();

        // Redaktur tidak bisa membuat galeri reporter
        $this->actingAs($redaktur)
            ->postJson(route('reporter.galleries.store'), ['title' => 'X'])
            ->assertForbidden();
    }
}
