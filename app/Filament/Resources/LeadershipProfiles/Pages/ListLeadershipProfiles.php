<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadershipProfiles\Pages;

use App\Filament\Resources\LeadershipProfiles\LeadershipProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListLeadershipProfiles extends ListRecords
{
    protected static string $resource = LeadershipProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
