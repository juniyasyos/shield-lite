<?php

namespace juniyasyos\ShieldLite\Resources\Users\Pages;

use Filament\Resources\Pages\CreateRecord;
use juniyasyos\ShieldLite\Resources\Users\UserResource;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}

