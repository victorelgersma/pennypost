<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
            {{ __('User Directory') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="pp-content-wrap space-y-4">
            <form method="GET" action="{{ route('directory.index') }}">
                <x-text-input type="text" name="q" value="{{ $query }}" placeholder="{{ __('Search by name…') }}"
                    class="w-full" />
            </form>

            @forelse ($people as $person)
                <div class="pp-paper-card p-4 sm:p-6 flex items-center justify-between gap-4 flex-wrap">
                    <div>
                        <p class="pp-serif font-medium" style="color: var(--ink);">{{ $person->name }}</p>
                        <p class="pp-mono text-xs mt-1" style="color: var(--ink-soft);">
                            {{ __('Joined') }} {{ $person->created_at->format('M Y') }}
                        </p>
                    </div>

                    <a href="{{ route('messages.create', ['to_id' => $person->id, 'to_name' => $person->name]) }}"
                    class="pp-btn pp-btn-ghost"
                    >
                    {{ __('Write') }}
                    </a>
                </div>
            @empty
                <div class="pp-paper-card p-6 text-sm" style="color: var(--ink-soft);">
                    {{ __('No one matches that search.') }}
                </div>
            @endforelse

            {{ $people->links() }}
        </div>
    </div>
</x-app-layout>