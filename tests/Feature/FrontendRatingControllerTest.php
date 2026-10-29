<?php

namespace Tests\Feature;

use App\Models\Post;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendRatingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_can_rate_post_and_average_is_updated(): void
    {
        $post = Post::factory()->published()->create();

        $this->postJson('/api/frontend/posts/'.$post->slug.'/rating', [
            'rating' => 4,
        ])
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.average', '4.00')
            ->assertJsonPath('data.count', 1);

        $this->assertDatabaseHas('ratings', [
            'post_id' => $post->id,
            'rating' => 4,
        ]);
        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'rating_average' => 4,
            'rating_count' => 1,
        ]);
    }

    public function test_same_visitor_cannot_rate_same_post_twice(): void
    {
        $post = Post::factory()->published()->create();

        $this->postJson('/api/frontend/posts/'.$post->slug.'/rating', ['rating' => 5])
            ->assertCreated();

        $this->postJson('/api/frontend/posts/'.$post->slug.'/rating', ['rating' => 3])
            ->assertStatus(400)
            ->assertJsonPath('message', 'Anda sudah memberikan rating untuk berita ini.');

        $this->assertDatabaseCount('ratings', 1);
    }

    public function test_rating_outside_allowed_range_is_rejected(): void
    {
        $post = Post::factory()->published()->create();

        $this->postJson('/api/frontend/posts/'.$post->slug.'/rating', ['rating' => 0])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('rating');

        $this->assertDatabaseCount('ratings', 0);
    }

    public function test_article_page_rating_section_uses_post_slug(): void
    {
        // Route binding {post} resolves by slug — section harus menyimpan
        // slug (bukan id) agar fetch rating tidak 404 "No query results".
        $post = Post::factory()->published()->create();

        $html = $this->get(route('posts.show', $post))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('data-post-slug="'.$post->slug.'"', $html);
        $this->assertStringNotContainsString('data-post-id=', $html);
        $this->assertStringContainsString('section.dataset.postSlug', $html);
        $this->assertStringContainsString('/api/frontend/posts/${postSlug}/rating', $html);

        // Fetch rating harus mengirim body JSON + header Content-Type;
        // jika tidak, Laravel tidak bisa parse body → "The rating field is required".
        $fetchPos = strpos($html, '/api/frontend/posts/${postSlug}/rating');
        $fetchBlock = substr($html, $fetchPos - 300, 800);
        $this->assertStringContainsString("'Content-Type': 'application/json'", $fetchBlock);
        $this->assertStringContainsString('JSON.stringify({ rating })', $fetchBlock);
    }
}
