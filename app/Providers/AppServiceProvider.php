<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Content\Models\Faq;
use App\Domain\Content\Models\LeadershipProfile;
use App\Domain\Content\Models\Page;
use App\Domain\Content\Models\Post;
use App\Domain\Content\Observers\PostObserver;
use App\Domain\Content\Policies\ContentPolicy;
use App\Domain\Content\Policies\PagePolicy;
use App\Domain\Identity\Listeners\RecordEmailDelivery;
use App\Domain\Membership\Models\MembershipCategory;
use App\Domain\Membership\Policies\MembershipCategoryPolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // The implementation plan's Phase 2 file list places layouts at
        // resources/views/layouts/{public,portal,form}.blade.php. Blade would
        // otherwise resolve <x-layouts::public> to views/components/layouts/,
        // so the namespace is registered rather than the directory moved —
        // the plan's structure is the approved one.
        Blade::anonymousComponentPath(resource_path('views/layouts'), 'layouts');

        /*
         * NOTE: email components are deliberately NOT registered as a `mail`
         * component namespace.
         *
         * Laravel already owns a `mail` view namespace for its Markdown mail
         * components, and registering another produced "No hint path defined
         * for [mail]" on every send. So the components live at
         * resources/views/components/mail/ and resolve as <x-mail.layout>
         * through Blade's default path, while the templates themselves stay at
         * resources/views/mail/ as the plan's Phase 7 file list specifies.
         *
         * A small deviation from that file list, forced by a framework
         * namespace that was already taken.
         */

        /*
         * FR-10.1 — the delivery log.
         *
         * Bound to Laravel's own mail events rather than called from each
         * mailable. FR-10.1 lists ten transactional emails, and the one that
         * forgets to log itself is the one somebody asks about; this also
         * covers Fortify's password reset and verification mail without
         * touching it.
         */
        Event::listen(MessageSending::class, [RecordEmailDelivery::class, 'sending']);
        Event::listen(MessageSent::class, [RecordEmailDelivery::class, 'sent']);

        // Laravel resolves a model's factory by stripping the "App\Models\"
        // prefix, so App\Domain\Identity\Models\User is guessed as
        // Database\Factories\Domain\Identity\Models\UserFactory — which does
        // not exist.
        //
        // The domain structure is required by TRD §1.2, so the resolver is
        // told about it once here rather than every model overriding
        // newFactory(). Factories keep a flat namespace: a model's basename
        // is unique across the four domains, and a second Media or Event
        // would be a naming problem worth having anyway.
        Factory::guessFactoryNamesUsing(
            static fn (string $model): string => 'Database\\Factories\\'.class_basename($model).'Factory'
        );

        // Laravel's policy auto-discovery expects App\Policies\; the domain
        // structure (TRD §1.2) puts them with the models they govern, so the
        // mapping is declared rather than guessed.
        //
        // Filament calls these for every resource action, so navigation,
        // buttons and direct URLs are all governed by the same check. There is
        // no separate "hide the menu item" path that could drift from the real
        // gate — which is what AC-F9 requires.
        // App Flow P-06 — a changed slug must leave a 301. Registered as an
        // observer so the guarantee holds for every path that changes a slug,
        // not only the admin panel.
        Post::observe(PostObserver::class);

        Gate::policy(Post::class, ContentPolicy::class);
        Gate::policy(LeadershipProfile::class, ContentPolicy::class);
        Gate::policy(Faq::class, ContentPolicy::class);
        Gate::policy(Page::class, PagePolicy::class);
        Gate::policy(MembershipCategory::class, MembershipCategoryPolicy::class);
    }
}
