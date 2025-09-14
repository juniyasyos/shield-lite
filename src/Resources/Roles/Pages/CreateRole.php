<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Pages;

use Filament\Resources\Pages\CreateRecord;
use juniyasyos\ShieldLite\Resources\Roles\RoleResource;
use Illuminate\Support\Facades\Auth;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return shield()->can('role.create');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['access'] = $data['gates'] ?? [];
        $data['guard'] = $data['guard_name'] ?? shield()->guard();
        $data['created_by_name'] = Auth::user()->name;

        unset($data['gates'], $data['guard_name'], $data['description'], $data['quick_actions'], $data['global_permission_tools']);

        return $data;
    }
}
