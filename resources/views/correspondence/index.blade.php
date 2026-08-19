<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
            {{ __('My correspondence') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($correspondences as $thread)
                <a href="{{ route('correspondence.show', $thread->person) }}" class="pp-letter-card p-4 sm:p-6 block hover:opacity-90 transition-opacity">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                        <p class="pp-serif font-medium text-lg" style="color: var(--ink);">{{ $thread->person->name }}</p>
                        <span class="pp-mono text-xs" style="color: var(--ink-soft);">
                            {{ $thread->latest->delivered_at->format('j M Y') }}
                        </span>
                    </div>
                    <p class="mt-2 text-sm truncate" style="color: var(--ink-soft);">
                        {{ $thread->latest->sender_id === auth()->id() ? __('You: ') : '' }}{{ $thread->latest->body }}
                    </p>
                    <p class="mt-2 pp-mono text-xs" style="color: var(--brass);">
                        {{ trans_choice('{1} :count letter|[2,*] :count letters', $thread->count, ['count' => $thread->count]) }}
                    </p>
                </a>
            @empty
                <div class="pp-letter-card p-6 text-sm space-y-3" style="color: var(--ink-soft);">
                    <p>{{ __("You don't have any delivered correspondence yet.") }}</p>
                    <a href="{{ route('directory.index') }}" class="pp-btn pp-btn-ghost">
                        {{ __('Find someone to write to') }}
                    </a>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
