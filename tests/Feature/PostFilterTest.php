<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_posts_index_renders_live_filters_without_filter_button(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->count(3)->for($admin, 'author')->create();

        $this->actingAs($admin)
            ->get(route('admin.posts.index'))
            ->assertOk()
            ->assertSee('id="post-search"', false)
            ->assertSee('id="post-status-filter"', false)
            ->assertSee('id="post-category-filter"', false)
            ->assertSee('id="posts-rows"', false)
            ->assertSee('id="posts-pagination"', false)
            ->assertDontSee('>Filter<', false);
    }

    public function test_admin_posts_filter_returns_filtered_rows_as_json(): void
    {
        $admin = User::factory()->admin()->create();

        $category = Category::factory()->create();
        $otherCategory = Category::factory()->create();

        $published = Post::factory()->published()->for($admin, 'author')->create([
            'category_id' => $category->id,
            'title' => 'Banjir melanda Kota Malang',
        ]);
        Post::factory()->for($admin, 'author')->create([
            'category_id' => $otherCategory->id,
            'title' => 'Berita Lainnya',
        ]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', [
                'search' => 'Banjir',
                'status' => 'published',
                'category' => $category->id,
            ]));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('page', 1)
            ->assertJsonPath('total', 1);

        $this->assertStringContainsString('Banjir melanda Kota Malang', $response->json('rows'));
        $this->assertStringNotContainsString('Berita Lainnya', $response->json('rows'));
    }

    public function test_admin_posts_filter_applies_pagination(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->count(25)->for($admin, 'author')->create();

        $response = $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['page' => 2]));

        $response->assertOk()
            ->assertJsonPath('page', 2)
            ->assertJsonPath('total', 25)
            ->assertJsonStructure(['rows', 'pagination']);
    }

    public function test_reporter_posts_index_renders_live_filters(): void
    {
        $reporter = User::factory()->reporter()->create();
        Post::factory()->count(2)->for($reporter, 'author')->create();

        $this->actingAs($reporter)
            ->get(route('reporter.posts.index'))
            ->assertOk()
            ->assertSee('id="post-search"', false)
            ->assertSee('id="post-status-filter"', false)
            ->assertSee('id="posts-rows"', false)
            ->assertDontSee('>Filter<', false);
    }

    public function test_reporter_posts_filter_only_returns_own_posts(): void
    {
        $reporter = User::factory()->reporter()->create();
        $other = User::factory()->reporter()->create();

        Post::factory()->published()->for($reporter, 'author')->create([
            'title' => 'Berita Milik Saya',
        ]);
        Post::factory()->published()->for($other, 'author')->create([
            'title' => 'Berita Orang Lain',
        ]);

        $response = $this->actingAs($reporter)
            ->getJson(route('reporter.posts.filter', ['status' => 'published']));

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('total', 1);

        $this->assertStringContainsString('Berita Milik Saya', $response->json('rows'));
        $this->assertStringNotContainsString('Berita Orang Lain', $response->json('rows'));
    }

    public function test_reporter_posts_filter_searches_by_title(): void
    {
        $reporter = User::factory()->reporter()->create();

        Post::factory()->create(['author_id' => $reporter->id, 'title' => 'Haul KH. Wahab Chasbullah']);
        Post::factory()->create(['author_id' => $reporter->id, 'title' => 'Musyawarah Kerja PWNU']);

        $response = $this->actingAs($reporter)
            ->getJson(route('reporter.posts.filter', ['search' => 'Haul']));

        $response->assertOk()->assertJsonPath('total', 1);
        $this->assertStringContainsString('Haul KH. Wahab Chasbullah', $response->json('rows'));
    }
}
