<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadershipProfiles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * Leadership list — screen D-13.
 */
final class LeadershipProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('sort_order')->label('Order')->sortable(),
                TextColumn::make('name')->searchable(),
                TextColumn::make('position')->searchable()->wrap(),
                IconColumn::make('is_published')->label('Published')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])])
            ->emptyStateHeading('No council profiles yet')
            ->emptyStateDescription('App Flow P-03: for a compliance association this page does more persuading than any other.');
    }
}
