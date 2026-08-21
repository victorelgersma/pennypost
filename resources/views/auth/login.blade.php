<x-guest-layout>
    @if (session('status') === 'login-link-sent')
        <div class="pp-stamp-badge text-sm mb-4">
            {{ __("Check your email — we've sent you a link to log in.") }}
        </div>
    @endif

    <p class="text-sm mb-4" style="color: var(--ink-soft);">
        {{ __("Enter your email and we'll send you a link to log in — no password needed.") }}
    </p>

    <form method="POST" action="{{ route('login.link') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end pt-2">
            <x-primary-button>
                {{ __('Email me a login link') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>