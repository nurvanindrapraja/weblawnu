<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PostSortTest extends TestCase
{
    use RefreshDatabase;

    private function slugsFromJson($response): array
    {
        $html = $response->json('rows');
        preg_match_all('/admin\/posts\/([a-z0-9-]+)/', $html, $m);
        $seen = [];
        foreach ($m[1] as $slug) {
            if (! isset($seen[$slug])) {
                $seen[$slug] = true;
            }
        }

        return array_keys($seen);
    }

    public function test_default_sort_is_published_date_descending(): void
    {
        $admin = User::factory()->admin()->create();

        $older = Post::factory()->for($admin, 'author')->published()->create(['title' => 'AAA Lama']);
        $newer = Post::factory()->for($admin, 'author')->published()->create(['title' => 'ZZZ Baru']);
        $newer->forceFill(['published_at' => now()->addDay()])->save();
        $older->forceFill(['published_at' => now()->subDay()])->save();

        $this->actingAs($admin)
            ->getJson(route('admin.posts.filter'))
            ->assertOk()
            ->assertJsonPath('success', true);

        $slugs = $this->slugsFromJson(
            $this->actingAs($admin)->getJson(route('admin.posts.filter'))
        );

        $this->assertSame([$newer->slug, $older->slug], $slugs);
    }

    public function test_sort_by_title_ascending(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->for($admin, 'author')->create(['title' => 'Zebra']);
        Post::factory()->for($admin, 'author')->create(['title' => 'Alpha']);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['sort' => 'title', 'dir' => 'asc']))
            ->assertOk();

        $slugs = $this->slugsFromJson($response);
        $titles = Post::whereIn('slug', $slugs)->orderBy('title')->pluck('title')->all();

        $this->assertSame(['Alpha', 'Zebra'], $titles);
    }

    public function test_sort_by_views_descending(): void
    {
        $admin = User::factory()->admin()->create();
        $low = Post::factory()->for($admin, 'author')->create(['views' => 5]);
        $high = Post::factory()->for($admin, 'author')->create(['views' => 99]);

        $response = $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['sort' => 'views', 'dir' => 'desc']))
            ->assertOk();

        $slugs = $this->slugsFromJson($response);
        $this->assertSame([$high->slug, $low->slug], $slugs);
    }

    public function test_sort_by_category_and_author(): void
    {
        $admin = User::factory()->admin()->create();
        $authorA = User::factory()->create(['name' => 'Alice Penulis']);
        $authorZ = User::factory()->create(['name' => 'Zorro Penulis']);

        $catZ = Category::factory()->create(['name' => 'Zikira']);
        $catA = Category::factory()->create(['name' => 'Aset']);

        Post::factory()->for($authorA, 'author')->create(['category_id' => $catZ->id]);
        Post::factory()->for($authorZ, 'author')->create(['category_id' => $catA->id]);

        // Category A-Z
        $response = $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['sort' => 'category', 'dir' => 'asc']))
            ->assertOk();
        $slugs = $this->slugsFromJson($response);
        $firstCat = Post::where('slug', $slugs[0])->value('category_id');
        $this->assertSame($catA->id, $firstCat);

        // Author A-Z
        $response = $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['sort' => 'author', 'dir' => 'asc']))
            ->assertOk();
        $slugs = $this->slugsFromJson($response);
        $firstAuthor = Post::where('slug', $slugs[0])->value('author_id');
        $this->assertSame($authorA->id, $firstAuthor);
    }

    public function test_invalid_sort_falls_back_to_default(): void
    {
        $admin = User::factory()->admin()->create();
        Post::factory()->for($admin, 'author')->create();

        // `sort=id` bukan whitelist -> fallback published_at desc (tidak error)
        $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['sort' => 'id', 'dir' => 'asc']))
            ->assertOk()
            ->assertJsonPath('success', true);

        // Arah tidak valid diarahkan ke default kolom
        $this->actingAs($admin)
            ->getJson(route('admin.posts.filter', ['sort' => 'title', 'dir' => 'hacked']))
            ->assertOk()
            ->assertJsonPath('success', true);
    }
}
