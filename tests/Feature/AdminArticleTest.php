<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminArticleTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        // `auth` runs before `admin`, so guests land on the buyer login.
        $this->get(route('admin.articles'))->assertRedirect(route('login'));
    }

    public function test_non_admins_are_sent_back_to_their_dashboard(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('admin.articles'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_admin_article_list_renders_saved_posts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Article::factory()->create(['title' => 'Sembako Segar']);

        $this->actingAs($admin)
            ->get(route('admin.articles'))
            ->assertOk()
            ->assertSee('Blog dan tips warung')
            ->assertSee('Sembako Segar');
    }

    public function test_admin_article_form_renders_for_create_and_edit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();

        $this->actingAs($admin)->get(route('admin.articles.create'))->assertOk()->assertSee('Tulis artikel');
        $this->actingAs($admin)->get(route('admin.articles.edit', $article))->assertOk()->assertSee('Edit artikel');
    }

    public function test_admin_publishes_a_new_article(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->post(route('admin.articles.store'), [
                'title' => 'Cara Memilih Sembako Segar',
                'excerpt' => 'Panduan singkat belanja sembako.',
                'body' => 'Isi artikel pertama.',
                'status' => Article::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('admin.articles'));

        $article = Article::firstWhere('slug', 'cara-memilih-sembako-segar');

        $this->assertNotNull($article);
        $this->assertSame($admin->id, $article->user_id);
        $this->assertSame(Article::STATUS_PUBLISHED, $article->status);
        $this->assertNotNull($article->published_at);
    }

    public function test_draft_articles_are_saved_without_a_publish_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Draft Artikel',
            'body' => 'Belum selesai.',
            'status' => Article::STATUS_DRAFT,
        ]);

        $article = Article::firstWhere('slug', 'draft-artikel');

        $this->assertSame(Article::STATUS_DRAFT, $article->status);
        $this->assertNull($article->published_at);
    }

    public function test_store_article_rejects_invalid_input(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->from(route('admin.articles.create'))
            ->post(route('admin.articles.store'), [
                'title' => '',
                'body' => '',
                'status' => 'terbitkan',
            ])
            ->assertSessionHasErrors([
                'title' => 'Judul artikel wajib diisi.',
                'body' => 'Isi artikel wajib diisi.',
                'status',
            ])
            ->assertRedirect(route('admin.articles.create'));

        $this->assertSame(0, Article::count());
    }

    public function test_admin_updates_an_article_and_keeps_the_publish_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create(['title' => 'Judul Lama', 'published_at' => now()->subDays(5)]);
        $publishedAt = $article->published_at;

        $this->actingAs($admin)
            ->put(route('admin.articles.update', $article), [
                'title' => 'Judul Baru',
                'body' => $article->body,
                'status' => Article::STATUS_PUBLISHED,
            ])
            ->assertRedirect(route('admin.articles'));

        $article->refresh();

        $this->assertSame('Judul Baru', $article->title);
        $this->assertSame('judul-baru', $article->slug);
        $this->assertTrue($article->published_at->equalTo($publishedAt));
    }

    public function test_slug_stays_unique_when_two_articles_share_a_title(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Article::factory()->create(['title' => 'Tips Warung', 'slug' => 'tips-warung']);

        $this->actingAs($admin)->post(route('admin.articles.store'), [
            'title' => 'Tips Warung',
            'body' => 'Isi berbeda.',
            'status' => Article::STATUS_DRAFT,
        ]);

        $this->assertDatabaseHas('articles', ['slug' => 'tips-warung-2']);
    }

    public function test_toggle_pulls_a_published_article_back_to_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create();

        $this->actingAs($admin)->patch(route('admin.articles.toggle', $article))->assertRedirect();

        $this->assertSame(Article::STATUS_DRAFT, $article->refresh()->status);
    }

    public function test_toggle_publishes_a_draft(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->draft()->create();

        $this->actingAs($admin)->patch(route('admin.articles.toggle', $article))->assertRedirect();

        $article->refresh();

        $this->assertSame(Article::STATUS_PUBLISHED, $article->status);
        $this->assertNotNull($article->published_at);
    }

    public function test_admin_searches_articles_by_title(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        Article::factory()->create(['title' => 'Sembako Segar']);
        Article::factory()->create(['title' => 'Kopi Susu']);

        $this->actingAs($admin)
            ->get(route('admin.articles', ['search' => 'sembako']))
            ->assertOk()
            ->assertSee('Sembako Segar')
            ->assertDontSee('Kopi Susu');
    }

    public function test_cover_upload_replaces_the_previous_file(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create(['cover_path' => 'articles/lama.jpg']);

        $this->actingAs($admin)->put(route('admin.articles.update', $article), [
            'title' => $article->title,
            'body' => $article->body,
            'status' => Article::STATUS_PUBLISHED,
            'cover' => UploadedFile::fake()->image('cover.jpg'),
        ]);

        $article->refresh();

        $this->assertNotSame('articles/lama.jpg', $article->cover_path);
        Storage::disk('public')->assertExists($article->cover_path);
    }

    public function test_admin_deletes_an_article_with_its_cover(): void
    {
        Storage::fake('public');

        $admin = User::factory()->create(['role' => 'admin']);
        $article = Article::factory()->create(['cover_path' => 'articles/hapus.jpg']);
        Storage::disk('public')->put('articles/hapus.jpg', 'x');

        $this->actingAs($admin)->delete(route('admin.articles.destroy', $article))->assertRedirect();

        $this->assertDatabaseMissing('articles', ['id' => $article->id]);
        Storage::disk('public')->assertMissing('articles/hapus.jpg');
    }
}
