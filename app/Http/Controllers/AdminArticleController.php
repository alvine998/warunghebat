<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class AdminArticleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->input('search', ''));

        return view('admin.articles', [
            'articles' => Article::query()
                ->with('author:id,name')
                ->when($search !== '', function ($query) use ($search): void {
                    $query->where(function ($query) use ($search): void {
                        $query->where('title', 'like', "%{$search}%")
                            ->orWhere('excerpt', 'like', "%{$search}%")
                            ->orWhere('body', 'like', "%{$search}%");
                    });
                })
                ->latest('id')
                ->paginate(15)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.article-form', ['article' => new Article]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        $article = Article::create($this->attributes($request, $validated));

        return redirect()
            ->route('admin.articles')
            ->with('success', "Artikel \"{$article->title}\" disimpan.");
    }

    public function edit(Article $article): View
    {
        return view('admin.article-form', ['article' => $article]);
    }

    public function update(Request $request, Article $article): RedirectResponse
    {
        $validated = $this->validated($request);

        $article->update($this->attributes($request, $validated, $article));

        return redirect()
            ->route('admin.articles')
            ->with('success', "Artikel \"{$article->title}\" diperbarui.");
    }

    public function toggle(Article $article): RedirectResponse
    {
        $publishing = $article->status !== Article::STATUS_PUBLISHED;

        $article->update([
            'status' => $publishing ? Article::STATUS_PUBLISHED : Article::STATUS_DRAFT,
            // Keep the original date when a post is pulled and published again.
            'published_at' => $publishing ? ($article->published_at ?? now()) : $article->published_at,
        ]);

        return back()->with('success', $publishing
            ? "Artikel \"{$article->title}\" terbit dan tampil di /artikel."
            : "Artikel \"{$article->title}\" ditarik jadi draft.");
    }

    public function destroy(Article $article): RedirectResponse
    {
        if ($article->cover_path) {
            Storage::disk('public')->delete($article->cover_path);
        }

        $article->delete();

        return back()->with('success', "Artikel \"{$article->title}\" dihapus.");
    }

    /** @return array<string, mixed> */
    protected function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:140'],
            'excerpt' => ['nullable', 'string', 'max:300'],
            'body' => ['required', 'string', 'max:20000'],
            'status' => ['required', 'in:'.implode(',', Article::STATUSES)],
            'cover' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'title.required' => 'Judul artikel wajib diisi.',
            'title.max' => 'Judul maksimal 140 karakter.',
            'excerpt.max' => 'Ringkasan maksimal 300 karakter.',
            'body.required' => 'Isi artikel wajib diisi.',
            'body.max' => 'Isi artikel maksimal 20.000 karakter.',
            'status.required' => 'Status artikel wajib dipilih.',
            'status.in' => 'Status artikel tidak valid.',
            'cover.image' => 'File harus berupa gambar.',
            'cover.mimes' => 'Format cover harus JPG, PNG, atau WebP.',
            'cover.max' => 'Ukuran cover maksimal 2MB.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    protected function attributes(Request $request, array $validated, ?Article $article = null): array
    {
        $publishing = $validated['status'] === Article::STATUS_PUBLISHED;

        $attributes = [
            'user_id' => $article?->user_id ?? Auth::id(),
            'title' => $validated['title'],
            'slug' => Article::uniqueSlug($validated['title'], $article?->id),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'status' => $validated['status'],
            'published_at' => $publishing ? ($article?->published_at ?? now()) : $article?->published_at,
        ];

        if ($request->hasFile('cover')) {
            if ($article?->cover_path) {
                Storage::disk('public')->delete($article->cover_path);
            }

            $attributes['cover_path'] = $request->file('cover')->store('articles', 'public');
        }

        return $attributes;
    }
}
