<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipCategories\Tables;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Category list — FR-2.2, screen D-06.
 */
final class MembershipCategoriesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),

                TextColumn::make('number_prefix')->label('Prefix')->color('gray'),

                // Tabular figures, right aligned: money reads as a fact
                // (UI brief §5.5).
                TextColumn::make('annual_fee_kobo')
                    ->label('Annual fee')
                    ->alignEnd()
                    ->formatStateUsing(fn (int $state): string => '₦'.number_format($state / 100))
                    ->sortable(),

                IconColumn::make('requires_verification')
                    ->label('Verified')
                    ->boolean()
                    ->tooltip('Requires a certificate and an administrator decision before membership is created.'),

                IconColumn::make('is_active')->label('Open')->boolean(),

                TextColumn::make('memberships_count')
                    ->label('Members')
                    ->counts('memberships')
                    ->alignEnd(),
            ])
            ->defaultSort('sort_order')
            ->recordActions([
                EditAction::make(),
                // AC-F2 — deactivating removes a category from the join flow
                // but preserves its members. Deleting one with members would
                // orphan them, and ON DELETE RESTRICT refuses it at the
                // database anyway; hiding the action keeps the interface
                // honest rather than offering something that will fail.
                DeleteAction::make()
                    ->visible(fn ($record): bool => $record->memberships()->doesntExist()),
            ])
            ->emptyStateHeading('No membership categories yet')
            ->emptyStateDescription(
                'The join flow stays closed until at least one category is active. '
                .'Real categories and fees are blocker B-3.'
            );
    }
}
