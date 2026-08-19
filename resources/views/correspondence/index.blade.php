<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
            {{ __('My correspondence') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            @if ($correspondences->isEmpty())
                <div class="pp-letter-card p-6 text-sm space-y-3" style="color: var(--ink-soft);">
                    <p>{{ __("You don't have any delivered correspondence yet.") }}</p>
                    <a href="{{ route('directory.index') }}" class="pp-btn pp-btn-ghost">
                        {{ __('Find someone to write to') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    @foreach ($correspondences as $thread)
                        <a href="{{ route('correspondence.show', $thread->person) }}" class="pp-letter-card p-6 flex items-center justify-center text-center hover:opacity-90 transition-opacity aspect-square">
                            <p class="pp-serif font-medium" style="color: var(--ink);">{{ $thread->person->name }}</p>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>