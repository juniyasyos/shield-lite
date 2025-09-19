<?php

namespace juniyasyos\ShieldLite\Resources\Roles\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('guard_name', shield()->guard()))
            ->columns([
                TextColumn::make('name')
                    ->searchable()
                    ->label(__('Role Name')),
                TextColumn::make('permissions_count')
                    ->counts('permissions')
                    ->label(__('Permissions')),
                TextColumn::make('users_count')
                    ->counts('users')
                    ->label(__('Users')),
                TextColumn::make('created_at')
                    ->sortable()
                    ->dateTime('d/m/y H:i'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->button()
                    ->visible(fn() => shield()->can('role.update')),
                DeleteAction::make()
                    ->button()
                    ->visible(fn() => shield()->can('role.delete')),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn() => shield()->can('role.delete')),
                ]),
            ]);
    }
}
