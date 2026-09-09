<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

/**
 * News post form — FR-7.1, FR-7.2, AC-F7, screen D-11.
 *
 * C-8: Filament's component patterns are accepted as they are. Colour,
 * typeface and radius come from the panel's token theming; nothing here
 * restyles a Filament component. That restraint is what keeps the admin from
 * becoming the schedule risk the plan warns it is.
 *
 * The bar for this phase (NFR 6.6): a non-technical administrator can publish,
 * schedule, edit and unpublish a post without a developer. Every helper text
 * below exists because an administrator will otherwise ask.
 */
final class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(220)
                ->live(onBlur: true)
                ->afterStateUpdated(function (string $operation, $state, callable $set): void {
                    // Only on create. Regenerating the slug on every edit
                    // would silently change the URL of a post that has
                    // already been shared (App Flow P-06).
                    if ($operation === 'create') {
                        $set('slug', Str::slug((string) $state));
                    }
                })
                ->columnSpanFull(),

            TextInput::make('slug')
                ->required()
                ->maxLength(240)
                ->unique(ignoreRecord: true)
                ->helperText('Changing this leaves a redirect from the old address, so links already shared keep working.')
                ->columnSpanFull(),

            Textarea::make('excerpt')
                ->maxLength(400)
                ->rows(2)
                ->helperText('Shown on the news index and in social previews.')
                ->columnSpanFull(),

            RichEditor::make('body')
                ->required()
                ->columnSpanFull(),

            Select::make('status')
                ->required()
                ->default('draft')
                ->live()
                ->options([
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                ])
                ->helperText('A draft is not reachable by anybody outside this panel, even with the link.'),

            DateTimePicker::make('published_at')
                ->label('Publish at')
                ->seconds(false)
                ->timezone('Africa/Lagos')
                // The database CHECK refuses a published row with no date.
                // Requiring it here turns a constraint violation into a field
                // error the editor can act on.
                ->required(fn (callable $get): bool => in_array($get('status'), ['published', 'scheduled'], true))
                ->helperText('A scheduled post appears by itself at this time. Nothing else to do.'),

            Select::make('author_user_id')
                ->label('Author')
                ->relationship('author', 'full_name')
                ->required()
                ->default(fn () => auth()->id())
                ->searchable()
                ->preload(),

            Select::make('terms')
                ->label('Categories and tags')
                ->relationship('terms', 'name')
                ->multiple()
                ->preload(),

            TextInput::make('meta_title')
                ->maxLength(200)
                ->helperText('Leave blank to use the title.'),

            Textarea::make('meta_description')
                ->maxLength(320)
                ->rows(2)
                ->helperText('Shown under the title when the post is shared or found in search.'),
        ])->columns(2);
    }
}
