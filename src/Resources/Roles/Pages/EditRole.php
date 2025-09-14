<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use juniyasyos\ShieldLite\Resources\Roles\RoleResource;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make()
                ->visible(fn() => shield()->can('role.delete')),
        ];
    }

    public static function canAccess(array $parameters = []): bool
    {
        return shield()->can('role.update');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['gates'] = $data['access'];
        $data['guard_name'] = $data['guard'] ?? 'web';
        
        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['access'] = $data['gates'] ?? [];
        $data['guard'] = $data['guard_name'] ?? $data['guard'] ?? 'web';

        unset($data['gates'], $data['guard_name'], $data['description'], $data['quick_actions'], $data['global_permission_tools']);

        return $data;
    }
}
