<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Pages;

use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use juniyasyos\ShieldLite\Resources\Roles\RoleResource;
use juniyasyos\ShieldLite\Services\RoleService;
use juniyasyos\ShieldLite\Support\ShieldLogger;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    private RoleService $roleService;
    protected array $pendingPermissions = [];

    public function boot(): void
    {
        parent::boot();
        $this->roleService = app(RoleService::class);
    }

    public static function canAccess(array $parameters = []): bool
    {
        $canAccess = shield()->can('role.create');

        if (!$canAccess) {
            ShieldLogger::warning('Unauthorized access attempt to create role page', [
                'user_id' => auth()->id()
            ]);
        }

        return $canAccess;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        try {
            ShieldLogger::debug('Preparing role data for creation', [
                'data_keys' => array_keys($data)
            ]);

            // Set proper guard name
            $data['guard_name'] = $data['guard_name'] ?? shield()->guard();

            // Store permissions for after create - handle array safely
            $this->pendingPermissions = [];
            if (isset($data['gates'])) {
                if (is_array($data['gates'])) {
                    $this->pendingPermissions = $data['gates'];
                } else {
                    ShieldLogger::warning('Gates field is not array, converting', [
                        'gates_type' => gettype($data['gates']),
                        'gates_value' => $data['gates']
                    ]);
                    $this->pendingPermissions = [$data['gates']];
                }
            }

            // Remove non-database fields
            unset($data['gates'], $data['description'], $data['quick_actions'], $data['global_permission_tools']);

            ShieldLogger::debug('Role data prepared for creation', [
                'name' => $data['name'] ?? 'unknown',
                'permissions_count' => count($this->pendingPermissions),
                'guard_name' => $data['guard_name']
            ]);

            return $data;

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to prepare role data for creation', ['data' => $data], $e);
            throw $e;
        }
    }

    protected function afterCreate(): void
    {
        try {
            ShieldLogger::info('Creating role with permissions', [
                'role_id' => $this->getRecord()->id,
                'role_name' => $this->getRecord()->name,
                'permissions_count' => count($this->pendingPermissions)
            ]);

            // Use service to safely sync permissions
            if (!empty($this->pendingPermissions)) {
                $this->roleService->syncRolePermissions(
                    $this->getRecord(),
                    $this->pendingPermissions
                );
            }

            Notification::make()
                ->title('Role created successfully')
                ->body("Role '{$this->getRecord()->name}' has been created with " . count($this->pendingPermissions) . " permissions.")
                ->success()
                ->send();

            ShieldLogger::role('created', $this->getRecord()->name, [
                'role_id' => $this->getRecord()->id,
                'permissions_count' => count($this->pendingPermissions)
            ]);

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to create role permissions', [
                'role_id' => $this->getRecord()->id,
                'permissions' => $this->pendingPermissions
            ], $e);

            Notification::make()
                ->title('Error creating permissions')
                ->body('Role was created but failed to assign permissions: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
