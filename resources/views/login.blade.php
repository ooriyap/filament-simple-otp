@push('styles')
    <link rel="stylesheet"
        href="{{ \Filament\Support\Facades\FilamentAsset::getStyleHref(
            'filament-simple-otp-styles',
            'ooriyap/filament-simple-otp'
        ) }}">
@endpush
<x-filament-panels::page.simple>
    <div class="fi-login-content space-y-6">
        {{-- Filament Native Tabs --}}
        <x-filament::tabs :label="__('filament-simple-otp::login.login.login_mode')">
            <x-filament::tabs.item class="w-full" :active="$loginMode === 'password'"
                wire:click="setLoginMode('password')" tag="button" type="button">
                {{ __('filament-simple-otp::login.login.mode_password') }}
            </x-filament::tabs.item>
            <x-filament::tabs.item class="w-full" :active="$loginMode === 'otp'" wire:click="setLoginMode('otp')"
                tag="button" type="button">
                {{ __('filament-simple-otp::login.login.mode_otp') }}
            </x-filament::tabs.item>
        </x-filament::tabs>

        @if ($loginMode === 'otp')
            {{-- OTP Login Form --}}
            @if (!$codeSent)
                <form wire:submit="sendOtpCode" class="space-y-6">
                    <div>
                        <label for="mobile" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            {{ __('filament-simple-otp::login.login.mobile') }}
                        </label>
                        <x-filament::input.wrapper :valid="!$errors->has('mobile')">
                            <x-filament::input type="tel" id="mobile" wire:model="mobile"
                                :placeholder="__('filament-simple-otp::login.login.mobile_placeholder')" dir="ltr" required
                                autofocus />
                        </x-filament::input.wrapper>
                        @error('mobile')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <x-filament::button type="submit" size="lg" class="w-full">
                        <span wire:loading.remove
                            wire:target="sendOtpCode">{{ __('filament-simple-otp::login.login.send_code') }}</span>
                        <span wire:loading wire:target="sendOtpCode">...</span>
                    </x-filament::button>
                </form>
            @else
                <form wire:submit="loginWithOtp" class="space-y-6">
                    <div
                        class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-900/50 rounded-lg border border-gray-200 dark:border-gray-800 text-sm">
                        <span
                            class="text-gray-600 dark:text-gray-400">{{ __('filament-simple-otp::login.login.code_sent_to', ['mobile' => $mobile]) }}</span>
                        <button type="button" wire:click="resetForm"
                            class="text-xs text-primary-600 dark:text-primary-400 hover:underline">
                            {{ __('filament-simple-otp::login.login.change_number') }}
                        </button>
                    </div>

                    <div>
                        <label for="otpCode" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                            {{ __('filament-simple-otp::login.login.otp_code') }}
                        </label>
                        <x-filament::input.wrapper :valid="!$errors->has('otpCode')">
                            <x-filament::input type="text" id="otpCode" wire:model="otpCode" placeholder="------" dir="ltr"
                                maxlength="6" required autofocus class="sms-auth-otp-input" />
                        </x-filament::input.wrapper>
                        @error('otpCode')
                            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Countdown Timer & Resend --}}
                    <div x-data="{
                                                seconds: @entangle('countdown'),
                                                timer: null,
                                                init() {
                                                    this.startTimer();
                                                    $watch('seconds', (val) => {
                                                        if (val > 0 && !this.timer) this.startTimer();
                                                    });
                                                },
                                                startTimer() {
                                                    clearInterval(this.timer);
                                                    this.timer = setInterval(() => {
                                                        if (this.seconds > 0) {
                                                            this.seconds--;
                                                        } else {
                                                            clearInterval(this.timer);
                                                            this.timer = null;
                                                        }
                                                    }, 1000);
                                                }
                                            }" class="text-center text-sm text-gray-500 dark:text-gray-400">
                        <template x-if="seconds > 0">
                            <p class="text-sm text-gray-600 dark:text-gray-400"
                                x-text="'{{ __('filament-simple-otp::login.login.resend_wait', ['seconds' => '__SEC__']) }}'.replace('__SEC__', seconds)">
                            </p>
                        </template>
                        <template x-if="seconds <= 0">
                            <button type="button" wire:click="sendOtpCode"
                                class="font-medium text-primary-600 dark:text-primary-400 hover:underline">
                                {{ __('filament-simple-otp::login.login.resend_code') }}
                            </button>
                        </template>
                    </div>

                    <x-filament::button type="submit" size="lg" class="w-full">
                        <span wire:loading.remove
                            wire:target="loginWithOtp">{{ __('filament-simple-otp::login.login.login') }}</span>
                        <span wire:loading wire:target="loginWithOtp">...</span>
                    </x-filament::button>
                </form>
            @endif
        @else
            {{-- Password Login Form --}}
            <form wire:submit="loginWithPassword" class="space-y-6">
                <div>
                    <label for="mobile_pwd" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        {{ __('filament-simple-otp::login.login.mobile') }}
                    </label>
                    <x-filament::input.wrapper :valid="!$errors->has('mobile')">
                        <x-filament::input type="tel" id="mobile_pwd" wire:model="mobile"
                            :placeholder="__('filament-simple-otp::login.login.mobile_placeholder')" dir="ltr" required
                            autofocus />
                    </x-filament::input.wrapper>
                    @error('mobile')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-200 mb-2">
                        {{ __('filament-simple-otp::login.login.password') }}
                    </label>
                    <x-filament::input.wrapper :valid="!$errors->has('password')">
                        <x-filament::input dir="ltr" type="password" id="password" wire:model="password" required />
                    </x-filament::input.wrapper>
                    @error('password')
                        <p class="mt-2 text-sm text-danger-600 dark:text-danger-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400 cursor-pointer">
                        <input type="checkbox" wire:model="remember"
                            class="fi-checkbox-input rounded border-gray-300 dark:border-gray-700 text-primary-600 shadow-sm focus:ring-primary-500">
                        <span>{{ __('filament-simple-otp::login.login.remember_me') }}</span>
                    </label>
                </div>

                <x-filament::button type="submit" size="lg" class="w-full">
                    <span wire:loading.remove
                        wire:target="loginWithPassword">{{ __('filament-simple-otp::login.login.mode_password') }}</span>
                    <span wire:loading wire:target="loginWithPassword">...</span>
                </x-filament::button>
            </form>
        @endif
    </div>
</x-filament-panels::page.simple>