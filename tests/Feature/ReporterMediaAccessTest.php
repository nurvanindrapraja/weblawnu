<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReporterMediaAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_can_open_media_library(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->get(route('reporter.media.index'))
            ->assertOk()
            ->assertDontSee('onclick="deleteMedia(', false)
            ->assertSee('id="media-search"', false);
    }

    public function test_reporter_media_filter_endpoint_works(): void
    {
        $reporter = User::factory()->reporter()->create();
        Media::factory()->create(['original_name' => 'khotbah-jumat.mp4']);

        $this->actingAs($reporter)
            ->getJson(route('reporter.media.filter', ['search' => 'khotbah']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 1);
    }

    public function test_redaktur_can_open_media_library(): void
    {
        $redaktur = User::factory()->redaktur()->create();

        $this->actingAs($redaktur)
            ->get(route('redaktur.media.index'))
            ->assertOk()
            ->assertDontSee('onclick="deleteMedia(', false);
    }

    public function test_admin_media_library_still_shows_delete_button(): void
    {
        $admin = User::factory()->admin()->create();
        Media::factory()->for($admin, 'uploader')->create();

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('onclick="deleteMedia(', false);
    }

    public function test_reporter_can_upload_media(): void
    {
        Storage::fake('public');
        $reporter = User::factory()->reporter()->create();
        $file = UploadedFile::fake()->image('foto-uji.jpg');

        $response = $this->actingAs($reporter)
            ->post(route('reporter.media.store'), ['file' => $file]);

        $response->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseCount('media', 1);
    }

    public function test_reporter_cannot_delete_media(): void
    {
        $admin = User::factory()->admin()->create();
        $media = Media::factory()->for($admin, 'uploader')->create();
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->delete(route('admin.media.destroy', $media))
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_reporter_can_delete_own_unpublished_media(): void
    {
        $reporter = User::factory()->reporter()->create();
        $media = Media::factory()->for($reporter, 'uploader')->create();

        $this->actingAs($reporter)
            ->delete(route('reporter.media.destroy', $media))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    public function test_reporter_cannot_delete_media_uploaded_by_another_user(): void
    {
        $other = User::factory()->reporter()->create();
        $media = Media::factory()->for($other, 'uploader')->create();
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->delete(route('reporter.media.destroy', $media))
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_reporter_cannot_delete_media_attached_to_published_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $media = Media::factory()->for($reporter, 'uploader')->create();
        $post = Post::factory()->published()->for($reporter, 'author')->create();
        $media->posts()->attach($post);

        $this->actingAs($reporter)
            ->delete(route('reporter.media.destroy', $media))
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_reporter_cannot_delete_featured_media_of_published_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $media = Media::factory()->for($reporter, 'uploader')->create();
        Post::factory()->published()->for($reporter, 'author')->create([
            'featured_media_id' => $media->id,
        ]);

        $this->actingAs($reporter)
            ->delete(route('reporter.media.destroy', $media))
            ->assertForbidden();

        $this->assertDatabaseHas('media', ['id' => $media->id]);
    }

    public function test_admin_can_delete_media_attached_to_published_post(): void
    {
        $admin = User::factory()->admin()->create();
        $reporter = User::factory()->reporter()->create();
        $media = Media::factory()->for($reporter, 'uploader')->create();
        $post = Post::factory()->published()->for($reporter, 'author')->create();
        $media->posts()->attach($post);

        $this->actingAs($admin)
            ->delete(route('admin.media.destroy', $media))
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertSoftDeleted('media', ['id' => $media->id]);
    }

    public function test_media_grid_shows_delete_button_only_for_reporters_own_unpublished_media(): void
    {
        $reporter = User::factory()->reporter()->create();
        $own = Media::factory()->for($reporter, 'uploader')->create(['original_name' => 'milik-saya.jpg']);
        $other = Media::factory()->create(['original_name' => 'milik-orang-lain.jpg']);

        $used = Media::factory()->for($reporter, 'uploader')->create(['original_name' => 'sudah-published.jpg']);
        $post = Post::factory()->published()->for($reporter, 'author')->create();
        $used->posts()->attach($post);

        $html = $this->actingAs($reporter)
            ->get(route('reporter.media.index'))
            ->assertOk()
            ->getContent();

        $card = function (string $name) use ($html): string {
            $start = strpos($html, $name);
            $next = strpos($html, 'overflow-hidden group relative', $start);

            return substr($html, $start, $next === false ? 600 : $next - $start);
        };

        $this->assertStringContainsString('onclick="deleteMedia(', $card('milik-saya.jpg'));
        $this->assertStringNotContainsString('onclick="deleteMedia(', $card('milik-orang-lain.jpg'));
        $this->assertStringNotContainsString('onclick="deleteMedia(', $card('sudah-published.jpg'));
    }

    public function test_media_grid_shows_upload_date_on_every_card(): void
    {
        $reporter = User::factory()->reporter()->create();
        $media = Media::factory()->for($reporter, 'uploader')->create([
            'original_name' => 'foto-tanggal.jpg',
            'created_at' => now()->startOfDay(),
        ]);

        $html = $this->actingAs($reporter)
            ->get(route('reporter.media.index'))
            ->assertOk()
            ->getContent();

        $card = substr($html, strpos($html, 'foto-tanggal.jpg'), 300);
        $this->assertStringContainsString($media->created_at->format('d M Y'), $card);
    }
}
