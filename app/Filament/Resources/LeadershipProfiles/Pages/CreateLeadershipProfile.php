<?php

declare(strict_types=1);

namespace App\Filament\Resources\LeadershipProfiles\Pages;

use App\Filament\Resources\LeadershipProfiles\LeadershipProfileResource;
use Filament\Resources\Pages\CreateRecord;

class CreateLeadershipProfile extends CreateRecord
{
    protected static string $resource = LeadershipProfileResource::class;
}
