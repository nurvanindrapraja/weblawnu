<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReporterDashboardTest extends TestCase
{
    use RefreshDatabase;

    private User $reporter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->reporter = User::factory()->reporter()->create();
    }

    public function test_dashboard_shows_sent_review_published_and_rejected_counts(): void
    {
        $this->seedPosts([
            'draft' => 2,
            'submitted' => 1,
            'review' => 1,
            'published' => 1,
            'rejected' => 1,
        ]);

        $this->actingAs($this->reporter)
            ->getJson(route('reporter.dashboard.statistics'))
            ->assertOk()
            ->assertJsonPath('data.posts.sent', 4)          // submitted + review + published + rejected
            ->assertJsonPath('data.posts.under_review', 2)   // submitted + review
            ->assertJsonPath('data.posts.published', 1)
            ->assertJsonPath('data.posts.rejected', 1)
            ->assertJsonPath('data.posts.draft', 2);
    }

    public function test_dashboard_page_renders_the_new_stat_cards(): void
    {
        $this->seedPosts(['submitted' => 1, 'published' => 1, 'rejected' => 1]);

        $this->actingAs($this->reporter)
            ->get(route('reporter.dashboard'))
            ->assertOk()
            ->assertSee('Berita Terkirim')
            ->assertSee('Sedang Direview')
            ->assertSee('Published')
            ->assertSee('Rejected');
    }

    /**
     * @param  array<string, int>  $counts
     */
    private function seedPosts(array $counts): void
    {
        foreach ($counts as $status => $count) {
            foreach (range(1, $count) as $i) {
                match ($status) {
                    'draft' => Post::factory()->for($this->reporter, 'author')->create(),
                    'published' => Post::factory()->published()->for($this->reporter, 'author')->create(),
                    default => Post::factory()->for($this->reporter, 'author')->create(['status' => $status]),
                };
            }
        }
    }
}
