<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Store;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_page_exposes_social_preview_tags(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.url('/').'"', false)
            ->assertSee('property="og:site_name" content="Warung Hebat"', false)
            ->assertSee('property="og:image" content="'.asset('og-image.png').'"', false)
            ->assertSee('property="og:image:width" content="1200"', false)
            ->assertSee('property="og:image:height" content="630"', false)
            ->assertSee('name="twitter:card" content="summary_large_image"', false)
            ->assertSee('name="robots" content="index, follow"', false)
            ->assertSee('"@type":"Organization"', false)
            ->assertSee('"@type":"WebSite"', false);
    }

    public function test_store_page_shares_its_own_title_description_and_photo(): void
    {
        $store = Store::factory()->create([
            'name' => 'Warung Bang Jago',
            'description' => 'Gorengan hangat tiap sore.',
            'image_path' => 'stores/bang-jago.jpg',
            'latitude' => -6.2297,
            'longitude' => 106.8294,
        ]);

        $this->get(route('store.show', $store))
            ->assertOk()
            ->assertSee('property="og:title" content="Warung Bang Jago — Warung Hebat"', false)
            ->assertSee('property="og:description" content="Gorengan hangat tiap sore."', false)
            ->assertSee(asset('storage/stores/bang-jago.jpg'), false)
            ->assertSee('"@type":"Store"', false)
            ->assertSee('"latitude":-6.2297', false);
    }

    public function test_category_page_describes_itself_for_search_engines(): void
    {
        $this->get(route('category.show', ['category' => 'sembako']))
            ->assertOk()
            ->assertSee('property="og:title" content="Sembako dari Warung Terdekat — Warung Hebat"', false)
            ->assertSee('property="og:description" content="Belanja Sembako dari warung di sekitarmu', false);
    }

    public function test_article_page_uses_article_metadata_and_cover(): void
    {
        $article = Article::factory()->create([
            'title' => 'Cara Memilih Sembako',
            'excerpt' => 'Panduan sembako segar.',
            'cover_path' => 'articles/sembako.jpg',
            'published_at' => now()->subDay(),
        ]);

        $this->get(route('articles.show', $article))
            ->assertOk()
            ->assertSee('property="og:type" content="article"', false)
            ->assertSee('property="og:description" content="Panduan sembako segar."', false)
            ->assertSee('property="article:published_time"', false)
            ->assertSee(asset('storage/articles/sembako.jpg'), false)
            ->assertSee('"@type":"Article"', false)
            ->assertSee('"headline":"Cara Memilih Sembako"', false);
    }

    public function test_about_page_is_indexable(): void
    {
        $this->get(route('about'))
            ->assertOk()
            ->assertSee('<link rel="canonical" href="'.route('about').'"', false)
            ->assertSee('name="robots" content="index, follow"', false)
            ->assertSee('"@type":"AboutPage"', false);
    }

    public function test_private_pages_are_noindex(): void
    {
        $buyer = User::factory()->create();

        $this->get(route('login'))->assertOk()->assertSee('name="robots" content="noindex, nofollow"', false);

        $this->actingAs($buyer)
            ->get(route('cart.index'))
            ->assertOk()
            ->assertSee('name="robots" content="noindex, nofollow"', false);
    }

    public function test_sitemap_lists_public_pages_and_skips_drafts(): void
    {
        $store = Store::factory()->create();
        $article = Article::factory()->create(['slug' => 'artikel-terbit']);
        Article::factory()->draft()->create(['slug' => 'artikel-rahasia']);

        $this->get('/sitemap.xml')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
            ->assertSee(route('home'), false)
            ->assertSee(route('store.index'), false)
            ->assertSee(route('articles.index'), false)
            ->assertSee(route('about'), false)
            ->assertSee(route('category.show', ['category' => 'sembako']), false)
            ->assertSee(route('articles.show', $article), false)
            ->assertSee(route('store.show', $store), false)
            ->assertDontSee('artikel-rahasia');
    }

    public function test_robots_txt_points_crawlers_to_the_sitemap(): void
    {
        $this->get('/robots.txt')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/plain; charset=UTF-8')
            ->assertSee('Sitemap: '.url('/sitemap.xml'))
            ->assertSee('Disallow: /backoffice');
    }
}
