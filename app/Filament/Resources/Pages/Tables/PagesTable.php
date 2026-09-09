<?php

declare(strict_types=1);

namespace App\Filament\Resources\Pages\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Page list — screen D-12.
 */
final class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->wrap(),
                TextColumn::make('slug')->searchable()->color('gray'),
                IconColumn::make('is_system')
                    ->label('Built in')
                    ->boolean()
                    ->tooltip('Built-in pages can be edited but not deleted or renamed.'),
                TextColumn::make('updated_at')
                    ->dateTime('j M Y')
                    ->timezone('Africa/Lagos')
                    ->sortable(),
            ])
            ->defaultSort('title')
            ->recordActions([
                EditAction::make(),
                // PagePolicy refuses a system page; hiding the button as well
                // keeps the interface honest rather than offering an action
                // that will be refused.
                DeleteAction::make()->visible(fn ($record): bool => ! $record->is_system),
            ])
            ->emptyStateHeading('No pages yet');
    }
}
