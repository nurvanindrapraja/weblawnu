<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Media;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostPreviewTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_preview_unpublished_post(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($admin, 'author')->create([
            'title' => 'Berita Draft Uji Preview',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.posts.preview', $post))
            ->assertOk()
            ->assertSee('Mode Preview', false)
            ->assertSee('Berita Draft Uji Preview')
            ->assertDontSee('id="rating-section"', false);
    }

    public function test_preview_page_renders_in_standards_mode(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($admin, 'author')->create();

        $content = $this->actingAs($admin)
            ->get(route('admin.posts.preview', $post))
            ->assertOk()
            ->baseResponse
            ->getContent();

        $this->assertStringStartsWith('<!DOCTYPE html>', $content);
    }

    public function test_preview_does_not_increment_views(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($admin, 'author')->create(['views' => 7]);

        $this->actingAs($admin)->get(route('admin.posts.preview', $post));

        $this->assertSame(7, $post->fresh()->views);
    }

    public function test_reporter_can_preview_own_post_but_not_others(): void
    {
        $reporter = User::factory()->reporter()->create();
        $other = User::factory()->reporter()->create();
        $own = Post::factory()->for($reporter, 'author')->create();
        $foreign = Post::factory()->for($other, 'author')->create();

        $this->actingAs($reporter)
            ->get(route('reporter.posts.preview', $own))
            ->assertOk();

        $this->actingAs($reporter)
            ->get(route('reporter.posts.preview', $foreign))
            ->assertForbidden();
    }

    public function test_redaktur_can_preview_post(): void
    {
        $redaktur = User::factory()->redaktur()->create();
        $post = Post::factory()->submitted()->create();

        $this->actingAs($redaktur)
            ->get(route('redaktur.posts.preview', $post))
            ->assertOk()
            ->assertSee('Mode Preview', false);
    }

    public function test_preview_draft_includes_featured_image_when_provided(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();
        $media = Media::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.posts.preview-draft'), [
                'title' => 'Berita Dengan Gambar',
                'content' => '<p>Isi berita</p>',
                'category_id' => $category->id,
                'featured_media_id' => $media->id,
            ])
            ->assertOk()
            ->assertSee('storage/'.$media->path, false);

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_create_edit_and_index_pages_expose_preview_controls(): void
    {
        $admin = User::factory()->admin()->create();
        $post = Post::factory()->for($admin, 'author')->create();

        $this->actingAs($admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('id="post-preview-modal"', false)
            ->assertSee("openPostPreviewGet('".route('admin.posts.preview', $post)."')", false);

        $this->actingAs($admin)
            ->get(route('admin.posts.create'))
            ->assertOk()
            ->assertSee('id="post-preview-modal"', false)
            ->assertSee('openPostPreviewPost', false);

        $this->actingAs($admin)
            ->get(route('admin.posts.edit', $post))
            ->assertOk()
            ->assertSee('id="post-preview-modal"', false)
            ->assertSee('openPostPreviewGet', false)
            ->assertSee(route('admin.posts.preview', $post), false);
    }

    public function test_guest_is_redirected_to_login_from_preview(): void
    {
        $post = Post::factory()->create();

        $this->get(route('admin.posts.preview', $post))->assertRedirect(route('login'));
    }

    public function test_admin_can_preview_unsaved_draft_without_persisting(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::factory()->create();

        $this->actingAs($admin)
            ->post(route('admin.posts.preview-draft'), [
                'title' => 'Berita Belum Disimpan',
                'content' => '<p>Isi berita lengkap untuk preview</p>',
                'category_id' => $category->id,
            ])
            ->assertOk()
            ->assertSee('Mode Preview', false)
            ->assertSee('Berita Belum Disimpan');

        $this->assertDatabaseCount('posts', 0);
    }

    public function test_preview_draft_redirects_back_with_validation_errors(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post(route('admin.posts.preview-draft'), [])
            ->assertRedirect()
            ->assertSessionHasErrors(['title', 'content', 'category_id']);
    }

    public function test_reporter_can_preview_unsaved_draft_without_persisting(): void
    {
        $reporter = User::factory()->reporter()->create();
        $category = Category::factory()->create();

        $this->actingAs($reporter)
            ->post(route('reporter.posts.preview-draft'), [
                'title' => 'Draft Reporter Preview',
                'content' => '<p>Isi berita</p>',
                'category_id' => $category->id,
            ])
            ->assertOk()
            ->assertSee('Draft Reporter Preview');

        $this->assertDatabaseCount('posts', 0);
    }
}
