<?php

namespace OoriyaP\FilamentSimpleOtp\Concerns;

use Filament\Panel;
use Illuminate\Support\Facades\Storage;

trait HasSimpleOtp
{
    public function initializeHasSimpleOtp(): void
    {
        $this->mergeFillable([
            'name',
            'mobile',
            'email',
            'avatar',
            'password',
            'is_active',
            'can_manage_admins',
        ]);

        $this->mergeCasts([
            'is_active' => 'boolean',
            'can_manage_admins' => 'boolean',
            'password' => 'hashed',
        ]);

        $this->makeHidden(['password', 'remember_token']);
    }

    public function canManageAdmins(): bool
    {
        return (bool) ($this->can_manage_admins ?? false);
    }

    public function getFilamentAvatarUrl(): ?string
    {
        if (empty($this->avatar)) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    public function canAccessPanel(?Panel $panel = null): bool
    {
        return (bool) ($this->is_active ?? true);
    }
}
