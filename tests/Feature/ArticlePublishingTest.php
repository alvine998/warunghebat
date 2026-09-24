<?php

namespace Tests\Feature;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticlePublishingTest extends TestCase
{
    use RefreshDatabase;

    public function test_article_index_lists_published_articles_newest_first(): void
    {
        Article::factory()->create(['title' => 'Artikel Lama', 'published_at' => now()->subDays(4)]);
        Article::factory()->create(['title' => 'Artikel Baru', 'published_at' => now()->subDay()]);

        $this->get('/artikel')->assertOk()->assertSeeInOrder(['Artikel Baru', 'Artikel Lama']);
    }

    public function test_article_index_hides_drafts_and_scheduled_posts(): void
    {
        Article::factory()->create(['title' => 'Artikel Terbit']);
        Article::factory()->draft()->create(['title' => 'Artikel Draft']);
        Article::factory()->scheduled()->create(['title' => 'Artikel Terjadwal']);

        $this->get('/artikel')
            ->assertOk()
            ->assertSee('Artikel Terbit')
            ->assertDontSee('Artikel Draft')
            ->assertDontSee('Artikel Terjadwal');
    }

    public function test_article_index_shows_an_empty_state_without_posts(): void
    {
        $this->get('/artikel')->assertOk()->assertSee('Belum ada artikel');
    }

    public function test_article_detail_renders_the_markdown_body(): void
    {
        $article = Article::factory()->create([
            'title' => 'Cara Memilih Sembako',
            'body' => "Pendahuluan artikel.\n\n## Cek sebelum bayar\n\n- Beras tidak berbau apek",
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Cara Memilih Sembako')
            ->assertSee('<h2>Cek sebelum bayar</h2>', false)
            ->assertSee('<li>Beras tidak berbau apek</li>', false);
    }

    public function test_article_detail_strips_raw_html_from_the_body(): void
    {
        $article = Article::factory()->create(['body' => "Aman kok.<script>alert('xss')</script>"]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertDontSee('<script>alert', false);
    }

    public function test_article_detail_returns_404_for_drafts_and_unknown_slugs(): void
    {
        $draft = Article::factory()->draft()->create();

        $this->get(route('articles.show', $draft))->assertNotFound();
        $this->get('/artikel/artikel-yang-tidak-ada')->assertNotFound();
    }

    public function test_article_detail_links_to_related_posts_and_hands_out_share_links(): void
    {
        $article = Article::factory()->create(['title' => 'Artikel Utama']);
        Article::factory()->create(['title' => 'Artikel Terkait']);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('Bagikan artikel ini')
            ->assertSee('https://wa.me/?text=', false)
            ->assertSee('Baca juga')
            ->assertSee('Artikel Terkait');
    }

    public function test_home_shows_the_three_latest_articles(): void
    {
        foreach (range(1, 5) as $i) {
            Article::factory()->create([
                'title' => "Artikel Nomor {$i}",
                'published_at' => now()->subDays(6 - $i),
            ]);
        }

        $this->get('/')
            ->assertOk()
            ->assertSee('Bacaan buat tetangga')
            ->assertSee('Artikel Nomor 5')
            ->assertSee('Artikel Nomor 4')
            ->assertSee('Artikel Nomor 3')
            ->assertDontSee('Artikel Nomor 2')
            ->assertDontSee('Artikel Nomor 1');
    }

    public function test_home_hides_the_article_section_when_nothing_is_published(): void
    {
        Article::factory()->draft()->create();

        $this->get('/')->assertOk()->assertDontSee('Bacaan buat tetangga');
    }
}
