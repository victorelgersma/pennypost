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
                        <div class="text-right">
                            @if ($letter->isDelivered())
                                <p class="pp-mono text-xs" style="color: var(--ink-soft);">
                                    {{ $letter->delivered_at->format('j F Y') }}
                                </p>
                            @else
                                <p class="pp-mono text-xs" style="color: var(--stamp-red);">
                                    {{ __('Sealed · arrives') }} {{ $letter->scheduled_for->format('j F Y') }}
                                </p>
                            @endif
                        </div>

                        <p class="pp-serif mt-6 text-center"
                            style="color: var(--ink); font-size: 1.25rem; line-height: 1.75;">
                            <span class="italic">{{ __('To') }}</span>
                            <span class="uppercase" style="margin-left: 0.75rem;">
                                {{ $letter->recipient_id === auth()->id() ? auth()->user()->name : $person->name }}
                            </span>
                        </p>

                        <p class="mt-8 whitespace-pre-wrap pp-serif"
                            style="color: var(--ink); font-size: 1.25rem; line-height: 1.75;">{{ $letter->body }}</p>

                        <p class="mt-8 text-right pp-serif"
                            style="color: var(--ink); font-size: 1.25rem; line-height: 1.75;">
                            [{{ $letter->sender_id === auth()->id() ? auth()->user()->name : $person->name }}]
                        </p>

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