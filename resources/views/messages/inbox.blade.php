<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
            {{ __('My correspondence') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($messages as $message)
                <div class="pp-letter-card p-4 sm:p-6">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                        <p class="pp-serif font-medium" style="color: var(--ink);">{{ $message->sender->name }}</p>
                        <p class="pp-mono text-xs" style="color: var(--ink-soft);">
                            {{ $message->delivered_at->format('j M Y, H:i') }} GMT
                        </p>
                    </div>
                    <p class="mt-2 whitespace-pre-wrap" style="color: var(--ink);">{{ $message->body }}</p>
                </div>
            @empty
                <div class="pp-letter-card p-6 text-sm" style="color: var(--ink-soft);">
                    {{ __('Nothing here yet. Delivered letters show up here every Friday at noon.') }}
                </div>
            @endforelse

            {{ $messages->links() }}
        </div>
    </div>
</x-app-layout>