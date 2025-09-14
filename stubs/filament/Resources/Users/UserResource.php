<?php

namespace App\Filament\Resources\Users;

use App\Models\User;
use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use juniyasyos\ShieldLite\HasShieldLite;
use juniyasyos\ShieldLite\Models\ShieldRole;

class UserResource extends Resource
{
    use HasShieldLite;

    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationGroup = 'User Management';

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

    public static function form(Form $form): Form
    {
        return $form
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
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => hexa()->can('user.update')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => hexa()->can('user.delete')),
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

