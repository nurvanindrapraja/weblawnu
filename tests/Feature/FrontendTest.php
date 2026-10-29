<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Menu;
use App\Models\Page;
use App\Models\Post;
use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FrontendTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_renders(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Lembaga Aset dan Wakaf')
            ->assertSee('LAWNU JATIM');
    }

    public function test_home_page_slideshow_shows_latest_published_posts(): void
    {
        $published = collect(range(1, 5))->map(
            fn (int $i) => Post::factory()->published()->create(['title' => 'Slide Berita '.$i])
        );
        Post::factory()->create(['title' => 'Berita Draf Tidak Muncul']);

        $response = $this->get('/');

        $response->assertOk()->assertSee('newsSlideshow');

        foreach ($published as $post) {
            $response->assertSee($post->title);
        }

        $response->assertDontSee('Berita Draf Tidak Muncul');
    }

    public function test_home_page_slideshow_is_limited_to_five_posts(): void
    {
        collect(range(1, 6))->each(function (int $i) {
            Post::factory()->published()->create([
                'title' => 'Berita Slide '.$i,
                'published_at' => now()->subDays(6 - $i),
            ]);
        });

        $response = $this->get('/');
        $response->assertOk();

        $html = $response->getContent();
        $slideshowMarkup = substr($html, 0, strpos($html, 'Berita Terbaru'));

        $this->assertStringContainsString('Berita Slide 6', $slideshowMarkup);
        $this->assertStringNotContainsString('Berita Slide 1', $slideshowMarkup);
        $this->assertStringContainsString('Berita Slide 1', $html);
    }

    public function test_berita_index_only_shows_published_posts(): void
    {
        Post::factory()->published()->create(['title' => 'Berita Sudah Terbit']);
        Post::factory()->create(['title' => 'Berita Masih Draf']);
        Post::factory()->approved()->create(['title' => 'Berita Baru Disetujui']);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('Berita Sudah Terbit')
            ->assertDontSee('Berita Masih Draf')
            ->assertDontSee('Berita Baru Disetujui');
    }

    public function test_berita_index_shows_nine_posts_per_page(): void
    {
        Setting::set('posts_per_page', '9', 'integer', 'display');

        foreach (range(1, 10) as $i) {
            Post::factory()->published()->create(['title' => sprintf('Berita %02d', $i)]);
        }

        $response = $this->get(route('posts.index'));

        $response->assertOk();

        foreach (range(2, 10) as $i) {
            $response->assertSee(sprintf('Berita %02d', $i));
        }

        $response->assertDontSee('Berita 01')
            ->assertSee('page=2', false);
    }

    public function test_berita_index_hides_dokumen_category_filter(): void
    {
        Category::factory()->create(['name' => 'Dokumen', 'slug' => 'dokumen']);
        Category::factory()->create(['name' => 'Kegiatan Uji', 'slug' => 'kegiatan-uji']);

        $this->get(route('posts.index'))
            ->assertOk()
            ->assertSee('Kegiatan Uji')
            ->assertDontSee('Dokumen');
    }

    public function test_post_detail_increments_view_count(): void
    {
        $post = Post::factory()->published()->create(['views' => 5]);

        $this->get('/berita/'.$post->slug)
            ->assertOk()
            ->assertSee($post->title);

        $this->assertDatabaseHas('posts', [
            'id' => $post->id,
            'views' => 6,
        ]);
    }

    public function test_unpublished_post_detail_returns_404(): void
    {
        $post = Post::factory()->approved()->create();

        $this->get('/berita/'.$post->slug)
            ->assertNotFound();
    }

    public function test_category_page_shows_only_published_posts_of_that_category(): void
    {
        $category = Category::factory()->create();
        Post::factory()->published()->create(['category_id' => $category->id, 'title' => 'Berita Kategori Uji']);
        Post::factory()->published()->create(['title' => 'Berita Kategori Lain']);

        $this->get('/kategori/'.$category->slug)
            ->assertOk()
            ->assertSee('Berita Kategori Uji')
            ->assertDontSee('Berita Kategori Lain');
    }

    public function test_category_page_shows_breadcrumb_back_to_news_index(): void
    {
        $category = Category::factory()->create(['name' => 'Kategori Uji']);

        $response = $this->get('/kategori/'.$category->slug);

        $response->assertOk()
            ->assertSee('href="/"', false)
            ->assertSee('href="/berita"', false)
            ->assertSee($category->name);
    }

    public function test_tentang_lawnu_page_shows_muktamar_name_change_info(): void
    {
        Page::factory()->create([
            'title' => 'Tentang LAWNU',
            'slug' => 'tentang-lawnu',
            'content' => '<p>Pada Muktamar ke-35 Nahdlatul Ulama yang diselenggarakan di Pondok Pesantren (Ponpes) Tambak Beras, Jombang, diputuskan pergantian nama LWP menjadi <strong>LAW (Lembaga Aset dan Wakaf)</strong>.</p>',
        ]);

        $this->get('/halaman/tentang-lawnu')
            ->assertOk()
            ->assertSee('Tentang LAWNU')
            ->assertSee('Muktamar ke-35')
            ->assertSee('Tambak Beras')
            ->assertSee('LAW (Lembaga Aset dan Wakaf)');
    }

    public function test_old_tentang_lwp_url_redirects_to_tentang_lawnu(): void
    {
        $this->get('/halaman/tentang-lwp')
            ->assertRedirect(route('pages.show', ['slug' => 'tentang-lawnu']));
    }

    public function test_top_menu_includes_dokumen_linking_to_dokumen_category(): void
    {
        Menu::create([
            'title' => 'DOKUMEN',
            'type' => 'category',
            'url' => '/kategori/dokumen',
            'sort_order' => 5,
            'is_active' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee('DOKUMEN')
            ->assertSee('/kategori/dokumen', false);
    }

    public function test_search_api_returns_only_published_posts(): void
    {
        $published = Post::factory()->published()->create(['title' => 'Kegiatan Serah Terima Wakaf']);
        Post::factory()->create(['title' => 'Draf Serah Terima Wakaf']);

        $this->getJson('/api/frontend/search?q=Wakaf')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data.posts')
            ->assertJsonPath('data.posts.0.id', $published->id);
    }

    public function test_search_api_returns_empty_results_for_short_query(): void
    {
        $this->getJson('/api/frontend/search?q=a')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(0, 'data.posts');
    }

    public function test_contact_form_stores_message(): void
    {
        $response = $this->postJson('/api/frontend/contact', [
            'name' => 'Budi Santoso',
            'email' => 'budi@example.com',
            'subject' => 'Pertanyaan Wakaf',
            'message' => 'Saya ingin bertanya tentang prosedur wakaf tanah.',
        ]);

        $response->assertOk()->assertJsonPath('success', true);

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'Budi Santoso',
            'subject' => 'Pertanyaan Wakaf',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_requires_email_or_phone(): void
    {
        $this->postJson('/api/frontend/contact', [
            'name' => 'Budi Santoso',
            'subject' => 'Pertanyaan Wakaf',
            'message' => 'Tanpa data kontak sama sekali.',
        ])
            ->assertStatus(422);

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
