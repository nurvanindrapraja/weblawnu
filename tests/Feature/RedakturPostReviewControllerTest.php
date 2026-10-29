<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RedakturPostReviewControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_redaktur_can_start_review_on_submitted_post(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->submitted()->create();

        $response = $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.start-review', $post));

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'review',
            'reviewed_by' => $redaktur->id,
        ]);
    }

    public function test_start_review_fails_when_post_is_not_submitted(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->approved()->create();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.start-review', $post))
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_redaktur_can_approve_post_under_review(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->inReview()->create();

        $response = $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.approve', $post));

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'approved',
            'reviewed_by' => $redaktur->id,
        ]);
    }

    public function test_redaktur_can_reject_post_with_note(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->submitted()->create();

        $response = $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.reject', $post), [
                'note' => 'Judul kurang informatif, tolong diperbaiki.',
            ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'rejected',
            'rejection_note' => 'Judul kurang informatif, tolong diperbaiki.',
        ]);
    }

    public function test_reject_requires_note(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->submitted()->create();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.reject', $post), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('note');
    }

    public function test_redaktur_can_publish_approved_post(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->approved()->create();

        $response = $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.publish', $post));

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'published',
            'published_by' => $redaktur->id,
        ]);
    }

    public function test_publish_fails_when_post_is_not_approved(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->inReview()->create();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.publish', $post))
            ->assertStatus(400)
            ->assertJsonPath('success', false);
    }

    public function test_redaktur_cannot_access_redaktur_area_as_reporter(): void
    {
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->get(route('redaktur.dashboard'))
            ->assertForbidden();
    }

    public function test_review_list_page_renders_only_pending_posts(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $pending = Post::factory()->submitted()->create(['title' => 'Berita Menunggu Review']);
        Post::factory()->published()->create(['title' => 'Berita Sudah Terbit']);

        $response = $this->actingAs($redaktur)
            ->get(route('redaktur.posts.review'));

        $response->assertOk()
            ->assertSee('Menunggu Review')
            ->assertSee('Berita Menunggu Review')
            ->assertDontSee('Berita Sudah Terbit')
            ->assertSee($pending->submitted_at->format('d M Y'));
    }

    public function test_review_detail_page_renders_and_starts_review(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->submitted()->create(['title' => 'Berita Siap Direview']);

        $response = $this->actingAs($redaktur)
            ->get(route('redaktur.posts.review-detail', $post));

        $response->assertOk()
            ->assertSee('Berita Siap Direview')
            ->assertSee('Setujui')
            ->assertSee('Tolak');

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'review',
            'reviewed_by' => $redaktur->id,
        ]);
    }

    public function test_review_detail_page_shows_banner_for_unreviewable_post(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->create(); // draft

        $this->actingAs($redaktur)
            ->get(route('redaktur.posts.review-detail', $post))
            ->assertOk()
            ->assertSee('tidak dapat direview lagi')
            ->assertDontSee("postAction('", false);
    }

    public function test_rejected_post_review_page_does_not_404_after_rejection(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->submitted()->create();

        // Redaktur membuka halaman review (status: submitted → review),
        // lalu menolak via endpoint.
        $this->actingAs($redaktur)
            ->get(route('redaktur.posts.review-detail', $post))
            ->assertOk();

        $this->actingAs($redaktur)
            ->patchJson(route('redaktur.posts.reject', $post), ['note' => 'Isi perlu diperbaiki'])
            ->assertOk()
            ->assertJsonPath('success', true);

        // Refresh/navigasi ulang ke halaman review tidak boleh 404.
        $html = $this->actingAs($redaktur)
            ->get(route('redaktur.posts.review-detail', $post))
            ->assertOk()
            ->assertSee('tidak dapat direview lagi')
            ->getContent();

        $this->assertStringNotContainsString("postAction('", $html);
        // Setelah aksi berhasil, JS mengarahkan ke daftar dengan filter status.
        $this->assertStringContainsString('status=rejected', $html);
    }
}
