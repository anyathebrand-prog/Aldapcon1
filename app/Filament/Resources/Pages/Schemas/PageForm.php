<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

/**
 * Page form — FR-1.1, FR-12.4, NFR 6.6, screen D-12.
 *
 * The six system pages are editable but not deletable: About, Contact, the
 * FAQ and the three legal pages are linked by slug from navigation, the footer
 * and the signup flow. The slug is therefore locked on a system page —
 * renaming it breaks those links exactly as deleting the page would, and the
 * delete guard alone would not catch it.
 */
final class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->required()
                ->maxLength(220)
                ->columnSpanFull(),

            TextInput::make('slug')
                ->required()
                ->maxLength(240)
                ->unique(ignoreRecord: true)
                ->disabled(fn ($record): bool => $record?->is_system === true)
                ->helperText(fn ($record): string => $record?->is_system === true
                    ? 'This address is fixed. The navigation and signup flow link to this page by name.'
                    : 'The address this page appears at.')
                ->columnSpanFull(),

            RichEditor::make('body')
                ->required()
                ->columnSpanFull(),

            TextInput::make('meta_title')->maxLength(200),

            Textarea::make('meta_description')->maxLength(320)->rows(2),
        ])->columns(2);
    }
}
