<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostEditorPageTest extends TestCase
{
    use RefreshDatabase;

    /**
     * TinyMCE refuses to initialize unless the document is in standards
     * mode. Any markup in a child view that sits OUTSIDE the @section
     * blocks is emitted before the parent layout's doctype, which pushes
     * the browser into quirks mode and silently breaks the editor.
     */
    public function test_admin_create_post_page_renders_in_standards_mode(): void
    {
        $admin = User::factory()->admin()->create();

        $content = $this->actingAs($admin)
            ->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee('class="rich-editor', false)
            ->assertSee('id="tag-modal"', false)
            ->assertSee('tinymce', false)
            ->baseResponse
            ->getContent();

        $this->assertStringStartsWith('<!DOCTYPE html>', $content);
    }

    public function test_admin_edit_post_page_renders_in_standards_mode(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($admin, 'author')->create();

        $content = $this->actingAs($admin)
            ->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSee('class="rich-editor', false)
            ->assertSee('id="tag-modal"', false)
            ->baseResponse
            ->getContent();

        $this->assertStringStartsWith('<!DOCTYPE html>', $content);
    }

    public function test_reporter_create_post_page_renders_in_standards_mode(): void
    {
        $reporter = User::factory()->reporter()->create();

        $content = $this->actingAs($reporter)
            ->get(route('reporter.posts.create'))
            ->assertOk()
            ->assertSee('class="rich-editor', false)
            ->baseResponse
            ->getContent();

        $this->assertStringStartsWith('<!DOCTYPE html>', $content);
    }

    public function test_reporter_edit_post_page_renders_in_standards_mode(): void
    {
        $reporter = User::factory()->reporter()->create();
        $post = Post::factory()->for($reporter, 'author')->create();

        $content = $this->actingAs($reporter)
            ->get(route('reporter.posts.edit', $post))
            ->assertOk()
            ->assertSee('class="rich-editor', false)
            ->baseResponse
            ->getContent();

        $this->assertStringStartsWith('<!DOCTYPE html>', $content);
    }
}
