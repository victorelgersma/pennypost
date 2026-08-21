<x-guest-layout>
    <p class="text-sm mb-4" style="color: var(--ink-soft);">
        {{ __("One more thing — what should we call you?") }}
    </p>

    <form method="POST" action="{{ route('onboarding.name.update') }}" class="space-y-4">
        @csrf

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end pt-2">
            <x-primary-button>
                {{ __('Continue') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
