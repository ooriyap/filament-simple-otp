<?php

namespace OoriyaP\FilamentSimpleOtp\Resources;

use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\IconSize;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;
use OoriyaP\FilamentSimpleOtp\Resources\AdminResource\Pages;
use OoriyaP\FilamentSimpleOtp\SimpleOtpPlugin;

class AdminResource extends Resource
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    public static function getNavigationLabel(): string
    {
        return __('filament-simple-otp::admin.navigation_label');
    }

    public static function getModelLabel(): string
    {
        return __('filament-simple-otp::admin.model_label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('filament-simple-otp::admin.plural_model_label');
    }

    public static function getModel(): string
    {
        return SimpleOtpPlugin::get()->getUserModel();
    }

    public static function canViewAny(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return (bool) (method_exists($user, 'canManageAdmins') ? $user->canManageAdmins() : ($user->can_manage_admins ?? false));
    }

    public static function canCreate(): bool
    {
        return static::canViewAny();
    }

    public static function canEdit(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function canDelete(Model $record): bool
    {
        $user = Filament::auth()->user();

        if ($user && $user->id === $record->id) {
            return false;
        }

        return static::canViewAny();
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('filament-simple-otp::admin.sections.account'))
                    ->schema([
                        FileUpload::make('avatar')
                            ->avatar()
                            ->image()
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->label(__('filament-simple-otp::admin.fields.avatar')),

                        TextInput::make('name')
                            ->label(__('filament-simple-otp::admin.fields.name'))
                            ->required()
                            ->maxLength(255),

                        TextInput::make('mobile')
                            ->label(__('filament-simple-otp::admin.fields.mobile'))
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),

                        TextInput::make('email')
                            ->label(__('filament-simple-otp::admin.fields.email'))
                            ->email()
                            ->nullable()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255),
                    ]),

                Section::make(__('filament-simple-otp::admin.sections.security'))
                    ->schema([
                        TextInput::make('password')
                            ->label(__('filament-simple-otp::admin.fields.password'))
                            ->password()
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->required(fn (string $operation): bool => $operation === 'create'),

                        Toggle::make('is_active')
                            ->label(__('filament-simple-otp::admin.fields.is_active'))
                            ->default(true),

                        Toggle::make('can_manage_admins')
                            ->label(__('filament-simple-otp::admin.fields.can_manage_admins'))
                            ->default(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('avatar')
                    ->label(__('filament-simple-otp::admin.columns.avatar'))
                    ->disk('public')
                    ->circular(),

                TextColumn::make('name')
                    ->label(__('filament-simple-otp::admin.columns.name'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('mobile')
                    ->label(__('filament-simple-otp::admin.columns.mobile'))
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label(__('filament-simple-otp::admin.columns.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('can_manage_admins')
                    ->label(__('filament-simple-otp::admin.columns.can_manage_admins'))
                    ->boolean(),

                IconColumn::make('is_active')
                    ->label(__('filament-simple-otp::admin.columns.is_active'))
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label(__('filament-simple-otp::admin.columns.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('can_manage_admins')
                    ->label(__('filament-simple-otp::admin.filters.can_manage_admins')),

                TernaryFilter::make('is_active')
                    ->label(__('filament-simple-otp::admin.filters.is_active')),
            ])
            ->actions([
                ActionGroup::make([
                    EditAction::make()
                        ->icon('heroicon-m-pencil-square'),
                    DeleteAction::make()
                        ->icon('heroicon-m-trash')
                ])
                    ->label(__('filament-simple-otp::admin.actions'))
                    ->dropdown(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
            'edit' => Pages\EditAdmin::route('/{record}/edit'),
        ];
    }
}
