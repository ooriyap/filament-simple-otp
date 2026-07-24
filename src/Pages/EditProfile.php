<?php

namespace OoriyaP\FilamentSimpleOtp\Pages;

use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Facades\Filament;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

/**
 * @property-read Schema $profileForm
 * @property-read Schema $passwordForm
 */
class EditProfile extends BaseEditProfile
{
    use WithRateLimiting;

    /**
     * @var array<string, mixed> | null
     */
    public ?array $profileData = [];

    /**
     * @var array<string, mixed> | null
     */
    public ?array $passwordData = [];

    public function getTitle(): string|Htmlable
    {
        return __('filament-simple-otp::profile.title');
    }

    public static function getNavigationLabel(): string
    {
        return __('filament-simple-otp::profile.title');
    }

    public function getView(): string
    {
        return 'filament-simple-otp::edit-profile';
    }

    public function getMaxContentWidth(): Width|string|null
    {
        return Filament::getCurrentPanel()?->getMaxContentWidth() ?? Width::SevenExtraLarge;
    }

    public function mount(): void
    {
        $this->fillForms();
    }

    protected function fillForms(): void
    {
        $user = $this->getUser();

        $this->profileForm->fill([
            'avatar' => $user->avatar ?? null,
            'name' => $user->name ?? '',
            'mobile' => $user->mobile ?? '',
            'email' => $user->email ?? '',
        ]);

        $this->passwordForm->fill([
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);
    }

    protected function getForms(): array
    {
        return [
            'profileForm',
            'passwordForm',
        ];
    }

    public function profileForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(! static::isSimple())
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('profileData')
            ->components([
                Section::make(__('filament-simple-otp::profile.sections.info.title'))
                    ->description(__('filament-simple-otp::profile.sections.info.description'))
                    ->schema([
                        FileUpload::make('avatar')
                            ->avatar()
                            ->image()
                            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                            ->maxSize(2048)
                            ->disk('public')
                            ->directory('avatars')
                            ->visibility('public')
                            ->label(__('filament-simple-otp::profile.fields.avatar')),

                        TextInput::make('name')
                            ->label(__('filament-simple-otp::profile.fields.name'))
                            ->nullable()
                            ->required()
                            ->maxLength(255),

                        TextInput::make('mobile')
                            ->label(__('filament-simple-otp::profile.fields.mobile'))
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('email')
                            ->label(__('filament-simple-otp::profile.fields.email'))
                            ->email()
                            ->nullable()
                            ->maxLength(255)
                            ->unique(table: $this->getUser()->getTable(), column: 'email', ignoreRecord: true),
                    ]),
            ]);
    }

    public function passwordForm(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(! static::isSimple())
            ->model($this->getUser())
            ->operation('edit')
            ->statePath('passwordData')
            ->components([
                Section::make(__('filament-simple-otp::profile.sections.password.title'))
                    ->description(__('filament-simple-otp::profile.sections.password.description'))
                    ->schema([
                        TextInput::make('current_password')
                            ->label(__('filament-simple-otp::profile.fields.current_password'))
                            ->password()
                            ->autocomplete('current-password')
                            ->revealable()
                            ->required(),

                        TextInput::make('new_password')
                            ->label(__('filament-simple-otp::profile.fields.new_password'))
                            ->password()
                            ->autocomplete('new-password')
                            ->revealable()
                            ->rule(Password::default())
                            ->required(),

                        TextInput::make('new_password_confirmation')
                            ->label(__('filament-simple-otp::profile.fields.new_password_confirmation'))
                            ->password()
                            ->autocomplete('new-password')
                            ->revealable()
                            ->same('new_password')
                            ->required(),
                    ]),
            ]);
    }

    public function updateProfile(): void
    {
        $rateKey = 'filament-edit-profile:'.Filament::auth()->id();

        if (RateLimiter::tooManyAttempts($rateKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            Notification::make()
                ->title(__('filament-simple-otp::profile.notifications.too_many_attempts_title'))
                ->body(__('filament-simple-otp::profile.notifications.too_many_attempts_body', ['seconds' => $seconds]))
                ->danger()
                ->send();

            return;
        }

        RateLimiter::hit($rateKey);

        $data = $this->profileForm->getState();
        $user = $this->getUser();

        $oldAvatar = $user->avatar;
        $newAvatar = $data['avatar'] ?? null;

        if ($oldAvatar && $oldAvatar !== $newAvatar) {
            Storage::disk('public')->delete($oldAvatar);
        }

        $user->update([
            'name' => $data['name'],
            'email' => $data['email'],
            'avatar' => $newAvatar,
        ]);

        Notification::make()
            ->title(__('filament-simple-otp::profile.notifications.profile_updated'))
            ->success()
            ->send();
    }

    public function updatePassword(): void
    {
        $rateKey = 'filament-change-password:'.Filament::auth()->id();

        if (RateLimiter::tooManyAttempts($rateKey, maxAttempts: 5)) {
            $seconds = RateLimiter::availableIn($rateKey);
            Notification::make()
                ->title(__('filament-simple-otp::profile.notifications.too_many_attempts_title'))
                ->body(__('filament-simple-otp::profile.notifications.too_many_attempts_body', ['seconds' => $seconds]))
                ->danger()
                ->send();

            return;
        }

        RateLimiter::hit($rateKey);

        $data = $this->passwordForm->getState();
        $user = $this->getUser();

        // Security Audit: Check current password matches
        if (filled($user->password) && ! Hash::check($data['current_password'], $user->password)) {
            throw ValidationException::withMessages([
                'passwordData.current_password' => __('filament-simple-otp::profile.validation.current_password_incorrect'),
            ]);
        }

        $newPasswordHash = Hash::make($data['new_password']);
        $user->update([
            'password' => $newPasswordHash,
        ]);

        // Security Update: Refresh session auth password hash
        if (request()->hasSession()) {
            request()->session()->put([
                'password_hash_'.Filament::getAuthGuard() => $newPasswordHash,
            ]);
        }

        $this->passwordForm->fill([
            'current_password' => '',
            'new_password' => '',
            'new_password_confirmation' => '',
        ]);

        Notification::make()
            ->title(__('filament-simple-otp::profile.notifications.password_updated'))
            ->success()
            ->send();
    }
}
