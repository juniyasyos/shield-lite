<?php

namespace juniyasyos\ShieldLite\Resources\Users;

use App\Models\User;
use BackedEnum;
use UnitEnum;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use juniyasyos\ShieldLite\HasShieldLite;
use juniyasyos\ShieldLite\Models\ShieldRole;
use Illuminate\Support\Collection;

class UserResource extends Resource
{
    use HasShieldLite;

    public static function getModel(): string
    {
        return config('shield.models.user', User::class);
    }

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';
    public static function getNavigationGroup(): UnitEnum|string|null
    {
        return __(config('shield.navigation.users_group', 'Settings'));
    }

    public static function getNavigationLabel(): string
    {
        return __(config('shield.navigation.users_label', 'Users'));
    }

    public function defineGates(): array
    {
        return [
            'user.index' => __('Allows viewing users'),
            'user.create' => __('Allows creating users'),
            'user.update' => __('Allows updating users'),
            'user.delete' => __('Allows deleting users'),
        ];
    }

    public static function canAccess(): bool
    {
        return hexa()->can('user.index');
    }

    public static function canCreate(): bool
    {
        return hexa()->can('user.create');
    }

    public static function canEdit($record): bool
    {
        return hexa()->can('user.update');
    }

    public static function canDelete($record): bool
    {
        return hexa()->can('user.delete');
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\TextInput::make('email')->email()->required()->maxLength(255),
                Forms\Components\TextInput::make('password')
                    ->password()
                    ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                    ->dehydrated(fn ($state) => filled($state))
                    ->confirmed()
                    ->revealable()
                    ->maxLength(255)
                    ->columnSpanFull(),
                // Optional: manage roles on create/edit via relationship
                Forms\Components\Select::make('roles')
                    ->label(__('Roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload()
                    ->searchable()
                    ->columnSpanFull(),
                Forms\Components\Select::make('default_role_id')
                    ->label(__('Default Role'))
                    ->options(fn () => ShieldRole::query()->pluck('name', 'id'))
                    ->helperText(__('Used as default role indicator for the user'))
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('email')->searchable()->sortable(),
                TextColumn::make('roles')->label('Roles')
                    ->state(fn ($record) => $record->roles?->pluck('name')->implode(', ') ?: '-')
                    ->toggleable(),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make()
                    ->visible(fn (User $record) => hexa()->can('user.update')),
                Action::make('setRoles')
                    ->label(__('Set Roles'))
                    ->icon('heroicon-o-lock-closed')
                    ->form([
                        Forms\Components\Select::make('roles')
                            ->label(__('Roles'))
                            ->options(fn () => ShieldRole::query()->pluck('name', 'id'))
                            ->multiple()
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('default_role_id')
                            ->label(__('Default Role'))
                            ->options(fn () => ShieldRole::query()->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->fillForm(fn (User $record) => [
                        'roles' => $record->roles()->pluck('id')->all(),
                        'default_role_id' => $record->default_role_id,
                    ])
                    ->action(function (User $record, array $data) {
                        $record->roles()->sync($data['roles'] ?? []);
                        if (! empty($data['default_role_id'])) {
                            $record->default_role_id = $data['default_role_id'];
                            $record->save();
                        }
                    })
                    ->visible(fn ($record) => hexa()->can('user.update')),
            ])
            ->toolbarActions([
                BulkAction::make('setRoles')
                    ->label(__('Set Roles'))
                    ->icon('heroicon-o-lock-closed')
                    ->form([
                        Forms\Components\Select::make('roles')
                            ->label(__('Roles'))
                            ->options(fn () => ShieldRole::query()->pluck('name', 'id'))
                            ->multiple()
                            ->required()
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('default_role_id')
                            ->label(__('Default Role'))
                            ->options(fn () => ShieldRole::query()->pluck('name', 'id'))
                            ->searchable(),
                    ])
                    ->action(function (Collection $records, array $data) {
                        foreach ($records as $record) {
                            $record->roles()->sync($data['roles'] ?? []);
                            if (! empty($data['default_role_id'])) {
                                $record->default_role_id = $data['default_role_id'];
                                $record->save();
                            }
                        }
                    })
                    ->visible(fn () => hexa()->can('user.update')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
