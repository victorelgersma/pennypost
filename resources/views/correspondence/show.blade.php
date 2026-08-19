<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
                {{ $person->name }}
            </h2>
            <a href="{{ route('messages.create', ['to_id' => $person->id, 'to_name' => $person->name]) }}" class="pp-btn pp-btn-solid">
                {{ __('Write') }}
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'message-sent')
                <div class="pp-stamp-badge text-sm">{{ __('Sealed! It will arrive on the next post day.') }}</div>
            @endif

            <a href="{{ route('correspondence.index') }}" class="pp-link text-sm">
                &larr; {{ __('All correspondence') }}
            </a>

            @forelse ($messages as $letter)
                <div class="pp-letter-card p-4 sm:p-6">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                        <p class="pp-serif font-medium" style="color: var(--ink);">
                            {{ $letter->sender_id === auth()->id() ? __('You') : $person->name }}
                        </p>

                        @if ($letter->isDelivered())
                            <p class="pp-mono text-xs" style="color: var(--ink-soft);">
                                {{ $letter->delivered_at->format('j M Y, H:i') }} GMT
                            </p>
                        @else
                            <span class="pp-mono text-xs px-2 py-1 border" style="border-color: var(--stamp-red); color: var(--stamp-red);">
                                {{ __('Sealed · arrives') }} {{ $letter->scheduled_for->format('j M, H:i') }} GMT
                            </span>
                        @endif
                    </div>

                    <p class="mt-2 whitespace-pre-wrap" style="color: var(--ink);">{{ $letter->body }}</p>

                    @if (! $letter->isDelivered() && $letter->canUnseal())
                        <form method="POST" action="{{ route('messages.unseal', $letter) }}"
                            class="mt-4 pt-4 border-t border-dashed flex items-center justify-between gap-4 flex-wrap" style="border-color: var(--line);">
                            @csrf
                            <p class="text-xs pp-mono" style="color: var(--ink-soft);">
                                {{ __('Unseal until') }} {{ $letter->unsealDeadline()->format('D, j M \a\t H:i') }} GMT
                                {{ __('to keep editing.') }}
                            </p>
                            <button type="submit" class="pp-btn pp-btn-ghost">
                                {{ __('Unseal & Edit') }}
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="pp-letter-card p-6 text-sm" style="color: var(--ink-soft);">
                    {{ __('Nothing here yet.') }}
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>