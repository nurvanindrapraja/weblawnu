<?php

namespace Tests\Feature;

use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminMediaControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_media_library_with_items(): void
    {
        $admin = User::factory()->admin()->create();
        $media = Media::factory()->create(['original_name' => 'foto-wakaf.jpg']);

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('foto-wakaf.jpg');
    }

    public function test_admin_can_view_empty_media_library(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('Belum ada media.');
    }

    public function test_media_library_filters_by_search_term(): void
    {
        $admin = User::factory()->admin()->create();
        Media::factory()->create(['original_name' => 'buku-wakaf.pdf']);
        Media::factory()->create(['original_name' => 'foto-kegiatan.jpg']);

        $this->actingAs($admin)
            ->get(route('admin.media.index', ['search' => 'buku']))
            ->assertOk()
            ->assertSee('buku-wakaf.pdf')
            ->assertDontSee('foto-kegiatan.jpg');
    }

    public function test_media_library_shows_25_items_per_page_newest_first(): void
    {
        $admin = User::factory()->admin()->create();

        foreach (range(1, 27) as $i) {
            Media::factory()->create(['original_name' => sprintf('media-%03d.jpg', $i)]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk();

        $html = $response->getContent();

        // 25 cards on the first page...
        $this->assertSame(25, substr_count($html, 'overflow-hidden group relative'));

        // ...and the newest upload (media-027) is the first card.
        $this->assertLessThan(
            strpos($html, 'media-026.jpg'),
            strpos($html, 'media-027.jpg'),
        );
        $this->assertStringNotContainsString('media-001.jpg', $html); // sits on page 2
    }

    public function test_media_search_input_is_live_without_filter_button(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.media.index'))
            ->assertOk()
            ->assertSee('id="media-search"', false)
            ->assertDontSee('name="search"', false);
    }

    public function test_media_filter_endpoint_returns_filtered_json(): void
    {
        $admin = User::factory()->admin()->create();
        Media::factory()->create(['original_name' => 'buku-wakaf.pdf']);
        Media::factory()->create(['original_name' => 'foto-kegiatan.jpg']);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.media.filter', ['search' => 'buku']))
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 1);

        $this->assertStringContainsString('buku-wakaf.pdf', $response->json('rows'));
    }

    public function test_reporter_cannot_access_media_library(): void
    {
        $this->actingAs(User::factory()->reporter()->create())
            ->get(route('admin.media.index'))
            ->assertForbidden();
    }
}
