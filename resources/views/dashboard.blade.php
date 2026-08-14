<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="pp-letter-card overflow-hidden">
                <div class="p-6" style="color: var(--ink);">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>