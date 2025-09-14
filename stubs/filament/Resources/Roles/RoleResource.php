<?php

namespace App\Filament\Resources\Roles;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use juniyasyos\ShieldLite\HasShieldLite;
use juniyasyos\ShieldLite\Models\ShieldRole;

class RoleResource extends Resource
{
    use HasShieldLite;

    protected static ?string $model = ShieldRole::class;

    protected static ?string $navigationIcon = 'heroicon-o-lock-closed';
    protected static ?string $navigationGroup = 'Settings';

    public function defineGates(): array
    {
        return [
            'role.index' => __('Access Roles & Permissions'),
            'role.create' => __('Create Role'),
            'role.update' => __('Update Role'),
            'role.delete' => __('Delete Role'),
        ];
    }

    public static function canAccess(): bool
    {
        return hexa()->can('role.index');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')->required()->maxLength(255),
                Forms\Components\Select::make('guard')->options([
                    'web' => 'web',
                    'admin' => 'admin',
                ])->default('web'),
                Forms\Components\TextInput::make('created_by_name')->label('Created By')->maxLength(255),
                Forms\Components\Textarea::make('access')
                    ->helperText(__('Paste JSON array of permission keys'))
                    ->rows(6)
                    ->dehydrateStateUsing(function ($state) {
                        if (blank($state)) return null;
                        $decoded = json_decode($state, true);
                        return is_array($decoded) ? $decoded : null;
                    })
                    ->formatStateUsing(fn ($state) => filled($state) ? json_encode($state, JSON_PRETTY_PRINT) : null)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('guard')->sortable(),
                TextColumn::make('created_by_name')->label('Created By')->sortable(),
                TextColumn::make('updated_at')->dateTime()->since()->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('edit')
                    ->label(__('Edit'))
                    ->icon('heroicon-o-pencil-square')
                    ->url(fn ($record) => static::getUrl('edit', ['record' => $record]))
                    ->visible(fn ($record) => hexa()->can('role.update')),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => hexa()->can('role.delete')),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'create' => Pages\CreateRole::route('/create'),
            'edit' => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}

