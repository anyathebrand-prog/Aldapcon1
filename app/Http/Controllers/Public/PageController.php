<?php

declare(strict_types=1);

namespace App\Http\Controllers\Public;

use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LeadershipProfile;
use App\Domain\Content\Models\Page;
use Illuminate\Contracts\View\View;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * P-02, P-03, P-09, P-12 to P-14 — FR-1.1, FR-1.3, FR-12.4.
 */
final class PageController
{
    public function show(string $slug): View
    {
        $page = Page::query()->where('slug', $slug)->first();

        if ($page === null) {
            throw new NotFoundHttpException;
        }

        return view('public.page', ['page' => $page]);
    }

    public function leadership(): View
    {
        // App Flow P-03 — "For a compliance association this page does more
        // persuading than any other."
        return view('public.leadership', [
            'profiles' => LeadershipProfile::query()
                ->where('is_published', true)
                ->with('photo')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function faq(): View
    {
        return view('public.faq', [
            'groups' => Faq::query()
                ->where('is_published', true)
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get()
                ->groupBy(fn (Faq $faq): string => $faq->group ?? 'General'),
        ]);
    }
}
