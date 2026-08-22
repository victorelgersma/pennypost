<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl text-[var(--ink)] leading-tight">
            {{ __('Drafts') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="pp-content-wrap space-y-4">
            @if (session('status') === 'draft-deleted')
                <div class="pp-stamp-badge text-sm">{{ __('Draft deleted.') }}</div>
            @endif

            @forelse ($drafts as $draft)
                <div class="pp-paper-card p-4 sm:p-6">
                    <a href="{{ route('messages.edit', $draft) }}" class="block hover:opacity-90 transition-opacity">
                        <div class="flex items-baseline justify-between gap-4 flex-wrap">
                            <p class="pp-serif font-medium" style="color: var(--ink);">
                                {{ $draft->recipient->name ?? __('No recipient yet') }}
                            </p>
                            <span class="pp-mono text-xs" style="color: var(--ink-soft);">
                                {{ __('Last touched') }} {{ $draft->updated_at->diffForHumans() }}
                            </span>
                        </div>
                        <p class="mt-2 text-sm line-clamp-2" style="color: var(--ink-soft);">
                            {{ $draft->body !== '' ? $draft->body : __('(empty letter)') }}
                        </p>
                    </a>

                    <form method="POST" action="{{ route('messages.destroy', $draft) }}"
                        onsubmit="return confirm('{{ __('Delete this draft for good?') }}')"
                        class="mt-3 pt-3 border-t border-dashed" style="border-color: var(--line);">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="text-xs pp-mono underline" style="color: var(--ink-soft);"
                            onmouseover="this.style.color='var(--error-red)'" onmouseout="this.style.color='var(--ink-soft)'">
                            {{ __('Delete this draft') }}
                        </button>
                    </form>
                </div>
            @empty
                <div class="pp-paper-card p-6 text-sm" style="color: var(--ink-soft);">
                    {{ __("No drafts sitting around. Start one whenever you're ready.") }}
                </div>
            @endforelse

            {{ $drafts->links() }}
        </div>
    </div>
</x-app-layout>