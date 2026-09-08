<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Content\Models\ContentTerm;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\SlugRedirect;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * P-05 news index and P-06 news post — FR-7.3, FR-7.4, AC-F7.
 */
final class NewsController
{
    public function index(Request $request): View
    {
        $category = $request->query('category');

        $posts = Post::query()
            ->published()
            ->with(['author', 'featuredImage'])
            ->when(is_string($category) && $category !== '', function ($query) use ($category): void {
                $query->whereHas('terms', fn ($q) => $q->where('slug', $category));
            })
            ->latest('published_at')
            ->paginate(10)
            ->withQueryString();

        return view('public.news.index', [
            'posts' => $posts,
            'categories' => ContentTerm::query()->where('type', 'category')->orderBy('name')->get(),
            'activeCategory' => is_string($category) ? $category : null,
        ]);
    }

    public function show(string $slug): View|RedirectResponse
    {
        $post = Post::query()->published()->where('slug', $slug)->first();

        if ($post === null) {
            /*
             * App Flow P-06: a changed slug must 301, "otherwise every shared
             * link the association has ever posted breaks".
             *
             * Checked only after the live lookup fails, so a redirect can
             * never shadow a real post.
             */
            $redirect = SlugRedirect::query()
                ->where('entity_type', 'post')
                ->where('old_slug', $slug)
                ->first();

            if ($redirect !== null) {
                $target = Post::query()->published()->find($redirect->entity_id);

                if ($target !== null) {
                    return redirect()->to('/news/'.$target->slug, 301);
                }
            }

            // AC-F7 — a draft or scheduled URL 404s rather than rendering.
            throw new NotFoundHttpException;
        }

        return view('public.news.show', [
            'post' => $post->load(['author', 'featuredImage', 'terms']),
            'related' => Post::query()
                ->published()
                ->whereKeyNot($post->getKey())
                ->latest('published_at')
                ->limit(3)
                ->get(),
        ]);
    }
}
