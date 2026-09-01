<x-guest-layout>
    <div x-data="{ sent: @js(session('status') === 'login-link-sent') }">
        <div x-show="sent" x-cloak>
            <div class="pp-stamp-badge text-sm mb-4">
                {{ __("Check your inbox — we've sent a link to log in.") }}
            </div>
            <p class="text-sm mb-4" style="color: var(--ink-soft);">
                {{ __("Didn't get it, or need to fix a typo?") }}
            </p>
            <button type="button" @click="sent = false" class="pp-btn pp-btn-ghost">
                {{ __('Try a different email') }}
            </button>
        </div>

        <div x-show="!sent" x-cloak>
            <p class="text-sm mb-4" style="color: var(--ink-soft);">
                {{ __("Enter your email and we'll send you a link to log in.") }}
            </p>

            <form method="POST" action="{{ route('login.link') }}" class="space-y-4"
                x-data="{ submitting: false }" @submit="submitting = true">
                @csrf

                <div style="position:absolute;left:-9999px;" aria-hidden="true">
                    <label for="website">Leave this field empty</label>
                    <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                </div>
                <div>
                    <x-input-label for="email" :value="__('Email')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required
                        autofocus autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end pt-2">
                    <x-primary-button x-bind:disabled="submitting">
                        <span x-show="!submitting" x-cloak class="inline-flex items-center gap-2">
                            {{ __('Continue with email') }}
                        </span>
                        <span x-show="submitting" x-cloak class="inline-flex items-center gap-2">
                            <span class="pp-spinner"></span>
                            {{ __('Sending…') }}
                        </span>
                    </x-primary-button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-sm mt-6" style="color: var(--ink-soft);">
        {{ __('New here?') }}
        <a href="{{ route('register') }}" class="pp-link">{{ __('Create an account') }}</a>
    </p>
</x-guest-layout>