<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4 flex-wrap">
            <div class="flex items-center gap-4">
                <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
                    {{ $person->name }}
                </h2>
                <a href="{{ route('messages.create', ['to_id' => $person->id, 'to_name' => $person->name]) }}"
                    class="pp-btn pp-btn-solid">
                    {{ __('Write') }}
                    <x-icons.pen />
                </a>
            </div>
        </div>
    </x-slot>
    <div class="py-12">
        <div class="mx-auto" style="max-width: 74rem; padding-inline: clamp(1.5rem, 6vw, 4rem);">
            <div class="flex items-start gap-10">
                @if ($letters->isNotEmpty())
                    <nav class="hidden md:block shrink-0"
                        style="width: 150px; position: sticky; top: 24px; max-height: calc(100vh - 48px); overflow-y: auto;">
                        <p class="pp-mono text-xs mb-3" style="color: var(--ink-soft); letter-spacing: 0.08em;">
                            {{ __('JUMP TO') }}
                        </p>
                        <ul class="space-y-2">
                            @foreach ($letters as $letter)
                                <li>
                                    <a href="#letter-{{ $letter->id }}" class="pp-mono text-xs block"
                                        style="color: var(--ink-soft); text-decoration: none; line-height: 1.5;"
                                        onmouseover="this.style.color='var(--ink)'"
                                        onmouseout="this.style.color='var(--ink-soft)'">
                                        {{ ($letter->delivered_at ?? $letter->sent_at)->format('jS F') }}
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                @endif

                <div class="flex-1 min-w-0" style="max-width: 57.5rem;">
                    @if (session('status') === 'message-sent')
                        <div class="pp-stamp-badge text-sm mb-4">
                            {{ __('Sealed! Your letter will arrive on :day the :date.', [
                                'day' => session('deliveryDayName'),
                                'date' => session('deliveryDayOrdinal'),
                            ]) }}
                        </div>
                    @endif

                    <div class="pp-letter-plain">
                        @forelse ($letters as $letter)
                            <div id="letter-{{ $letter->id }}" class="pp-letter-entry p-8 sm:p-12"
                                style="scroll-margin-top: 24px;">

                                <div class="text-right">
                                    @if ($letter->isDelivered())
                                        <p class="pp-serif" style="color: var(--ink-soft);">
                                            {{ $letter->delivered_at->format('j F Y') }}
                                        </p>
                                    @else
                                        <p class="pp-serif" style="color: var(--ink-soft);">
                                            {{ $letter->sent_at->format('j F Y') }}
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
                            </div>
                        @empty
                            <div class="p-8 sm:p-12 text-sm" style="color: var(--ink-soft);">
                                {{ __('Nothing here yet.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
