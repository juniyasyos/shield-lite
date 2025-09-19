<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Pages;

use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use juniyasyos\ShieldLite\Resources\Roles\RoleResource;
use juniyasyos\ShieldLite\Services\RoleService;
use juniyasyos\ShieldLite\Support\ShieldLogger;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    private RoleService $roleService;

    public function mount(int | string $record): void
    {
        $this->roleService = app(RoleService::class);

        try {
            parent::mount($record);

            ShieldLogger::info('Editing role page loaded', [
                'role_id' => $record,
                'user_id' => auth()->id()
            ]);

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to mount edit role page', ['role_id' => $record], $e);

            Notification::make()
                ->title('Error loading role')
                ->body('Failed to load role for editing.')
                ->danger()
                ->send();

            $this->redirect(RoleResource::getUrl('index'));
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => shield()->can('role.delete')),
        ];
    }

    protected function authorizeAccess(): void
    {
        if (!shield()->can('role.update')) {
            ShieldLogger::warning('Unauthorized access attempt to edit role', [
                'role_id' => $this->getRecord()?->id,
                'user_id' => auth()->id()
            ]);

            abort(403);
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        try {
            ShieldLogger::debug('Preparing role data for form', [
                'role_id' => $data['id'] ?? 'unknown',
                'data_keys' => array_keys($data)
            ]);

            // Get role with permissions
            $role = $this->roleService->getRoleWithPermissions($data['id']);

            // Convert permissions to gates array for form
            $data['gates'] = $role->permissions->pluck('name')->toArray();
            $data['guard_name'] = $role->guard_name;

            ShieldLogger::debug('Role data prepared for form', [
                'gates_count' => count($data['gates']),
                'guard_name' => $data['guard_name']
            ]);

            return $data;

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to prepare role data for form', [
                'data' => $data
            ], $e);

            // Return safe defaults
            return array_merge($data, [
                'gates' => [],
                'guard_name' => config('shield-lite.guard', 'web')
            ]);
        }
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        try {
            ShieldLogger::debug('Preparing role data for save', [
                'data_keys' => array_keys($data)
            ]);

            // Ensure gates is an array
            if (!isset($data['gates']) || !is_array($data['gates'])) {
                $data['gates'] = [];
                ShieldLogger::warning('Gates field was not an array, defaulted to empty array');
            }

            // Set proper guard name
            $data['guard_name'] = $data['guard_name'] ?? config('shield-lite.guard', 'web');

            return $data;

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to prepare role data for save', ['data' => $data], $e);
            throw $e;
        }
    }

    protected function afterSave(): void
    {
        try {
            ShieldLogger::info('Starting role permissions sync', [
                'role_id' => $this->getRecord()->id,
                'role_name' => $this->getRecord()->name
            ]);

            // Use service to safely sync permissions
            $this->roleService->syncRolePermissions(
                $this->getRecord(),
                $this->data['gates'] ?? []
            );

            Notification::make()
                ->title('Role updated successfully')
                ->body('Role and permissions have been updated.')
                ->success()
                ->send();

            ShieldLogger::role('updated', $this->getRecord()->name, [
                'role_id' => $this->getRecord()->id,
                'permissions_count' => count($this->data['gates'] ?? [])
            ]);

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to sync role permissions after save', [
                'role_id' => $this->getRecord()->id,
                'gates' => $this->data['gates'] ?? []
            ], $e);

            Notification::make()
                ->title('Error updating permissions')
                ->body('Role was saved but failed to update permissions: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function handleRecordUpdate(\Illuminate\Database\Eloquent\Model $record, array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            // Only update basic role data, permissions handled in afterSave
            $record->update([
                'name' => $data['name'],
                'guard_name' => $data['guard_name'] ?? $record->guard_name
            ]);

            return $record->fresh();

        } catch (\Throwable $e) {
            ShieldLogger::error('Failed to update role record', [
                'role_id' => $record->id,
                'data' => $data
            ], $e);

            throw $e;
        }
    }
}
