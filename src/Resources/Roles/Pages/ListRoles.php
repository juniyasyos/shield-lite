<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use juniyasyos\ShieldLite\Resources\Roles\RoleResource;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn() => shield()->can('role.create')),
        ];
    }
}
