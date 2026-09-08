<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

/**
 * News list — FR-7.1, screen D-10.
 *
 * Plan §2 rule 9: every list view ships its empty state in the same commit as
 * the list.
 */
final class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->limit(60)
                    ->wrap(),

                TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'published' => 'success',
                        'scheduled' => 'warning',
                        default => 'gray',
                    }),

                TextColumn::make('published_at')
                    ->label('Publish at')
                    ->dateTime('j M Y, H:i')
                    ->timezone('Africa/Lagos')
                    ->sortable()
                    ->placeholder('Not scheduled'),

                TextColumn::make('author.full_name')
                    ->label('Author')
                    ->toggleable(),
            ])
            ->defaultSort('published_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options([
                    'draft' => 'Draft',
                    'scheduled' => 'Scheduled',
                    'published' => 'Published',
                ]),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([DeleteBulkAction::make()]),
            ])
            ->emptyStateHeading('No posts yet')
            ->emptyStateDescription(
                'TRD §12 rates content not being ready as a launch risk in its own right: '
                .'a live site with no news reads as abandoned.'
            );
    }
}
