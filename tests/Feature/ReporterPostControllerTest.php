<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporterPostControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_reporter_can_create_draft_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $category = Category::factory()->create();

        $response = $this->actingAs($reporter)->postJson(route('reporter.posts.store'), [
            'title' => 'Berita Uji Coba Wakaf',
            'content' => 'Isi berita lengkap untuk uji coba.',
            'category_id' => $category->id,
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        $this->assertDatabaseHas('posts', [
            'author_id' => $reporter->id,
            'title' => 'Berita Uji Coba Wakaf',
            'status' => 'draft',
            'slug' => 'berita-uji-coba-wakaf',
            'category_id' => $category->id,
        ]);
    }

    public function test_create_post_returns_422_when_required_fields_missing(): void
    {
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->postJson(route('reporter.posts.store'), [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['title', 'content', 'category_id']);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_reporter_can_submit_draft_post_for_review(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->for($reporter, 'author')->create();

        $response = $this->actingAs($reporter)
            ->patchJson(route('reporter.posts.submit', $post));

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'status' => 'submitted',
        ]);
    }

    public function test_submitting_a_post_that_is_already_submitted_is_forbidden(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->submitted()->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->patchJson(route('reporter.posts.submit', $post))
            ->assertForbidden();
    }

    public function test_reporter_cannot_view_post_of_another_reporter(): void
    {
        $other = User::factory()->reporter()->create();
        $post = Post::factory()->for($other, 'author')->create();
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->get(route('reporter.posts.show', $post))
            ->assertForbidden();
    }

    public function test_reporter_cannot_delete_a_submitted_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->submitted()->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->deleteJson(route('reporter.posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'submitted']);
    }

    public function test_reporter_cannot_edit_a_published_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->published()->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->putJson(route('reporter.posts.update', $post), [
                'title' => 'Judul Diubah',
                'content' => 'Konten diubah.',
                'category_id' => $post->category_id,
            ])
            ->assertForbidden();
    }

    public function test_reporter_cannot_open_edit_page_for_published_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->published()->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->get(route('reporter.posts.edit', $post))
            ->assertForbidden();
    }

    public function test_reporter_cannot_edit_post_of_another_reporter(): void
    {
        $other = User::factory()->reporter()->create();
        $post = Post::factory()->for($other, 'author')->create(); // draft
        $reporter = User::factory()->reporter()->create();

        $this->actingAs($reporter)
            ->get(route('reporter.posts.edit', $post))
            ->assertForbidden();
    }

    public function test_reporter_cannot_delete_a_published_post(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->published()->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->deleteJson(route('reporter.posts.destroy', $post))
            ->assertForbidden();

        $this->assertDatabaseHas('posts', ['id' => $post->id, 'status' => 'published']);
    }

    public function test_create_page_binds_rich_editor_content_field_for_submission(): void
    {
        $reporter = User::factory()->reporter()->create();

        $html = $this->actingAs($reporter)
            ->get(route('reporter.posts.create'))
            ->assertOk()
            ->getContent();

        // TinyMCE disables the source textarea, so the form JS must set
        // `content` explicitly (regression: "The content field is required").
        $this->assertStringContainsString('<textarea name="content" id="content"', $html);
        $this->assertStringContainsString("formData.set('content',", $html);
    }

    public function test_edit_page_binds_rich_editor_content_field_for_submission(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->for($reporter, 'author')->create();

        $html = $this->actingAs($reporter)
            ->get(route('reporter.posts.edit', $post))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('<textarea name="content" id="content"', $html);
        $this->assertStringContainsString("formData.set('content',", $html);
    }
}
