<?php

namespace juniyasyos\ShieldLite\Resources\Users\Pages;

use Filament\Resources\Pages\ListRecords;
use Filament\Actions\CreateAction;
use juniyasyos\ShieldLite\Resources\Users\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn () => hexa()->can('user.create')),
        ];
    }
}
