<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl text-[var(--ink)] leading-tight">
            {{ __('Drafts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'draft-deleted')
                <div class="pp-stamp-badge text-sm">{{ __('Draft deleted.') }}</div>
            @endif

            @forelse ($drafts as $draft)
                <a href="{{ route('messages.edit', $draft) }}" class="pp-letter-card p-4 sm:p-6 block hover:opacity-90 transition-opacity">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                        <p class="pp-serif font-medium text-[var(--ink)]">
                            {{ $draft->recipient->name ?? __('No recipient yet') }}
                        </p>
                        <span class="pp-mono text-xs text-[var(--ink-soft)]">
                            {{ __('Last touched') }} {{ $draft->updated_at->diffForHumans() }}
                        </span>
                    </div>
                    <p class="mt-2 text-[var(--ink-soft)] text-sm line-clamp-2">
                        {{ $draft->body !== '' ? $draft->body : __('(empty letter)') }}
                    </p>
                </a>
            @empty
                <div class="pp-letter-card p-6 text-[var(--ink-soft)] text-sm">
                    {{ __("No drafts sitting around. Start one whenever you're ready.") }}
                </div>
            @endforelse

            {{ $drafts->links() }}
        </div>
    </div>
</x-app-layout>
