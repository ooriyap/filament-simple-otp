@php
    $pageComponent = static::isSimple() ? 'filament-panels::page.simple' : 'filament-panels::page';
@endphp

<x-dynamic-component :component="$pageComponent">
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2 xl:gap-8 items-start">
        {{-- Profile Information Form --}}
        <form wire:submit="updateProfile" class="space-y-6" autocomplete="off">
            {{ $this->profileForm }}

            <div class="fi-form-actions flex flex-wrap items-center justify-end gap-3 pt-2">
                <x-filament::button type="submit" size="md" icon="heroicon-m-check" wire:loading.attr="disabled"
                    wire:target="updateProfile">
                    <span wire:loading.remove wire:target="updateProfile">
                        {{ __('filament-simple-otp::profile.buttons.save_profile') }}
                    </span>
                    <span wire:loading wire:target="updateProfile" class="inline-flex items-center gap-2">
                        <x-filament::loading-indicator class="h-4 w-4" />
                        {{ __('filament-simple-otp::profile.buttons.saving') }}
                    </span>
                </x-filament::button>
            </div>
        </form>

        {{-- Password Change Form --}}
        <form wire:submit="updatePassword" class="space-y-6" autocomplete="off">
            {{ $this->passwordForm }}

            <div class="fi-form-actions flex flex-wrap items-center justify-end gap-3 pt-2">
                <x-filament::button type="submit" color="danger" size="md" icon="heroicon-m-key"
                    wire:loading.attr="disabled" wire:target="updatePassword">
                    <span wire:loading.remove wire:target="updatePassword">
                        {{ __('filament-simple-otp::profile.buttons.change_password') }}
                    </span>
                    <span wire:loading wire:target="updatePassword" class="inline-flex items-center gap-2">
                        <x-filament::loading-indicator class="h-4 w-4" />
                        {{ __('filament-simple-otp::profile.buttons.updating_password') }}
                    </span>
                </x-filament::button>
            </div>
        </form>
    </div>
</x-dynamic-component>