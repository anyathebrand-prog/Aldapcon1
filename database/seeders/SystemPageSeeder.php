<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Domain\Content\Models\Page;
use Illuminate\Database\Seeder;

/**
 * The six system pages — Schema §8.2, FR-1.1, FR-12.4.
 *
 * `is_system = true` means editable but not deletable: the navigation, the
 * footer and the signup flow all link to these by slug, so deleting one
 * breaks a hard-coded link rather than merely removing a page.
 *
 * Bodies are placeholders. PRD A7 assumes the association supplies real copy
 * before build completion, and TRD §12 treats content not being ready as a
 * launch risk in its own right — "a live site with placeholder bios and no
 * news reads as abandoned".
 */
final class SystemPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            'about' => 'About us',
            'contact' => 'Contact',
            'faq' => 'Frequently asked questions',
            'privacy-policy' => 'Privacy policy',
            'cookie-policy' => 'Cookie policy',
            'terms' => 'Terms of use',
        ];

        foreach ($pages as $slug => $title) {
            Page::firstOrCreate(
                ['slug' => $slug],
                [
                    'title' => $title,
                    'body' => "PLACEHOLDER — awaiting approved copy for “{$title}” (PRD A7).",
                    'meta_title' => $title.' — ALDAPCON',
                    'meta_description' => null,
                ]
            );

            // is_system is not fillable: a page becomes a system page by
            // seeder or migration, never by a form submission.
            Page::query()->where('slug', $slug)->update(['is_system' => true]);
        }
    }
}
