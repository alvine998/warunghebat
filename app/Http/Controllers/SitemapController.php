<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Public URLs crawlers should index: landing, catalogue, categories,
     * articles, about, and the storefronts themselves.
     */
    public function __invoke(): Response
    {
        $urls = [
            ['loc' => route('home'), 'changefreq' => 'daily', 'priority' => '1.0'],
            ['loc' => route('store.index'), 'changefreq' => 'daily', 'priority' => '0.9'],
            ['loc' => route('articles.index'), 'changefreq' => 'weekly', 'priority' => '0.7'],
            ['loc' => route('about'), 'changefreq' => 'monthly', 'priority' => '0.5'],
        ];

        foreach (Product::CATEGORIES as $category) {
            $urls[] = [
                'loc' => route('category.show', ['category' => Str::slug($category)]),
                'changefreq' => 'daily',
                'priority' => '0.8',
            ];
        }

        foreach (Article::query()->published()->latest('published_at')->get() as $article) {
            $urls[] = [
                'loc' => route('articles.show', $article),
                'lastmod' => $article->updated_at,
                'changefreq' => 'monthly',
                'priority' => '0.6',
            ];
        }

        foreach (Store::query()->latest('updated_at')->take(500)->get() as $store) {
            $urls[] = [
                'loc' => route('store.show', $store),
                'lastmod' => $store->updated_at,
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
