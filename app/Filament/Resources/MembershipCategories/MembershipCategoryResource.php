<?php

declare(strict_types=1);

namespace App\Filament\Resources\MembershipCategories;

use App\Domain\Membership\Models\MembershipCategory;
use App\Filament\Resources\MembershipCategories\Pages\CreateMembershipCategory;
use App\Filament\Resources\MembershipCategories\Pages\EditMembershipCategory;
use App\Filament\Resources\MembershipCategories\Pages\ListMembershipCategories;
use App\Filament\Resources\MembershipCategories\Schemas\MembershipCategoryForm;
use App\Filament\Resources\MembershipCategories\Tables\MembershipCategoriesTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MembershipCategoryResource extends Resource
{
    protected static ?string $model = MembershipCategory::class;

    protected static string|UnitEnum|null $navigationGroup = 'Members';

    protected static ?string $navigationLabel = 'Categories';

    protected static ?string $slug = 'membership-categories';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    public static function form(Schema $schema): Schema
    {
        return MembershipCategoryForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipCategoriesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembershipCategories::route('/'),
            'create' => CreateMembershipCategory::route('/create'),
            'edit' => EditMembershipCategory::route('/{record}/edit'),
        ];
    }
}
