<?php

namespace juniyasyos\ShieldLite\Resources\Users\Pages;

use Filament\Resources\Pages\ListRecords;
use juniyasyos\ShieldLite\Resources\Users\UserResource;

class ListUsers extends ListRecords
{
    protected static string $resource = UserResource::class;
}

