<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipCategories\Pages;

use App\Filament\Resources\MembershipCategories\MembershipCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMembershipCategory extends EditRecord
{
    protected static string $resource = MembershipCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
