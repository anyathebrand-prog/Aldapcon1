<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadershipProfiles\Pages;

use App\Filament\Resources\LeadershipProfiles\LeadershipProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditLeadershipProfile extends EditRecord
{
    protected static string $resource = LeadershipProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
