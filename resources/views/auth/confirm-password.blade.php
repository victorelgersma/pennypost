<x-guest-layout>
    <p class="text-sm mb-4" style="color: var(--ink-soft);">
        {{ __('This is a secure area. Please confirm your password before continuing.') }}
    </p>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end pt-2">
            <x-primary-button>
                {{ __('Confirm') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>