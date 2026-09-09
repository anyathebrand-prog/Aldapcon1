<?php

declare(strict_types=1);

namespace App\Domain\Content\Observers;

use App\Domain\Content\Models\Post;
use App\Domain\Content\Models\SlugRedirect;

/**
 * Keeps old post addresses working — App Flow P-06, Schema §2.6.
 *
 * "Slug changes must leave a redirect. Otherwise every shared link the
 * association has ever posted breaks."
 *
 * An observer rather than a Filament action, because the guarantee must hold
 * for every path that changes a slug — the panel, a console command, a future
 * import — not only the one an editor happens to use.
 *
 * updateOrCreate, because a slug can travel: a → b → a. Without it the second
 * change violates the unique index on (entity_type, old_slug) and an editor
 * sees a database error for renaming a post twice.
 */
final class PostObserver
{
    public function updating(Post $post): void
    {
        if (! $post->isDirty('slug')) {
            return;
        }

        $previous = $post->getOriginal('slug');

        if (! is_string($previous) || $previous === '') {
            return;
        }

        SlugRedirect::query()->updateOrCreate(
            ['entity_type' => 'post', 'old_slug' => $previous],
            ['entity_id' => $post->getKey()]
        );

        // A redirect pointing at the slug the post has just taken would be a
        // loop. Removing it also frees the address if the post moves back.
        SlugRedirect::query()
            ->where('entity_type', 'post')
            ->where('old_slug', $post->slug)
            ->delete();
    }
}
