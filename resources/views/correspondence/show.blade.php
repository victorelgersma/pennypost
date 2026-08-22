<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
                {{ $person->name }}
            </h2>
            <a href="{{ route('messages.create', ['to_id' => $person->id, 'to_name' => $person->name]) }}"
                class="pp-btn pp-btn-solid">
                {{ __('Write') }}
            </a>
        </div>
    </x-slot>
    <div>
        <div class="pp-content-wrap">
            @if (session('status') === 'message-sent')
                <div class="pp-stamp-badge text-sm mb-4">{{ __('Sealed! It will arrive on the next post day.') }}</div>
            @endif
            <div class="pp-letter-plain">
                @forelse ($messages as $letter)
                    <div class="pp-letter-entry p-8 sm:p-12">
                        <p class="pp-serif text-right" style="color: var(--ink); font-size: 1.0625rem; line-height: 1.7;">
                            <em>
                                {{ __('From') }}
                                {{ $letter->sender_id === auth()->id() ? auth()->user()->name : $person->name }}
                                {{ __('to') }}
                                {{ $letter->recipient_id === auth()->id() ? auth()->user()->name : $person->name }}
                                —
                                @if ($letter->isDelivered())
                                    {{ $letter->delivered_at->format('j M Y') }}
                                @else
                                    <span style="color: var(--stamp-red);">
                                        {{ __('Sealed · arrives') }} {{ $letter->scheduled_for->format('j M') }}
                                    </span>
                                @endif
                            </em>
                        </p>
                        <p class="mt-3 whitespace-pre-wrap pp-serif"
                            style="color: var(--ink); font-size: 1.0625rem; line-height: 1.7;">{{ $letter->body }}</p>
                        @if (!$letter->isDelivered() && $letter->canUnseal())
                            <form method="POST" action="{{ route('messages.unseal', $letter) }}"
                                class="mt-4 pt-4 border-t border-dashed flex items-center justify-between gap-4 flex-wrap"
                                style="border-color: var(--line);">
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
                    <div class="p-8 sm:p-12 text-sm" style="color: var(--ink-soft);">
                        {{ __('Nothing here yet.') }}
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>