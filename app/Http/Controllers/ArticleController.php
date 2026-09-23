<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\View\View;

class ArticleController extends Controller
{
    public function index(): View
    {
        return view('artikel.index', [
            'articles' => Article::query()
                ->published()
                ->with('author:id,name')
                ->latest('published_at')
                ->paginate(9)
                ->withQueryString(),
            'seoTitle' => 'Artikel & Tips Warung',
            'seoDescription' => 'Tips belanja hemat, panduan kulakan, dan cerita mitra warung — ditulis untuk pembeli dan pemilik warung di Indonesia.',
        ]);
    }

    public function show(Article $article): View
    {
        abort_unless($article->isPublished(), 404);

        return view('artikel.show', [
            'article' => $article->loadMissing('author:id,name'),
            'related' => Article::query()
                ->published()
                ->whereKeyNot($article->getKey())
                ->latest('published_at')
                ->take(3)
                ->get(),
            'seoTitle' => $article->title,
            'seoDescription' => $article->summary,
            'seoImage' => $article->cover_url,
            'seoType' => 'article',
            'seoPublishedAt' => $article->published_at,
        ]);
    }
}
