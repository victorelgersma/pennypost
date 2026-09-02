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

            @if ($messages->hasPages())
                <div class="flex items-center" style="gap: 20px;">
                    @if ($messages->previousPageUrl())
                        <a href="{{ $messages->previousPageUrl() }}"
                            style="font-size: 13px; color: var(--ink-soft); text-decoration: none;"
                            onmouseover="this.style.textDecoration='underline'; this.style.color='var(--ink)'"
                            onmouseout="this.style.textDecoration='none'; this.style.color='var(--ink-soft)'">
                            {{ __('← Next') }}
                        </a>
                    @else
                        <span style="font-size: 13px; color: var(--line); cursor: default;">
                            {{ __('← Next') }}
                        </span>
                    @endif
                    @if ($messages->hasMorePages())
                        <a href="{{ $messages->nextPageUrl() }}"
                            style="font-size: 13px; color: var(--ink-soft); text-decoration: none;"
                            onmouseover="this.style.textDecoration='underline'; this.style.color='var(--ink)'"
                            onmouseout="this.style.textDecoration='none'; this.style.color='var(--ink-soft)'">
                            {{ __('Previous →') }}
                        </a>
                    @else
                        <span style="font-size: 13px; color: var(--line); cursor: default;">
                            {{ __('Previous →') }}
                        </span>
                    @endif
                </div>
            @endif
        </div>

        @php
            $currentLetter = $messages->first();
        @endphp

        @if ($currentLetter && !$currentLetter->isDelivered() && $currentLetter->canUnseal())
            <div class="flex items-center justify-between gap-4 flex-wrap mt-4 pt-4"
                style="border-top: 1px solid var(--line);">
                <p class="text-xs pp-mono" style="color: var(--ink-soft);">
                    {{ __('Unseal until') }} {{ $currentLetter->unsealDeadline()->format('D, j M \a\t H:i') }} GMT
                    {{ __('to keep editing.') }}
                </p>
                <form method="POST" action="{{ route('messages.unseal', $currentLetter) }}">
                    @csrf
                    <button type="submit" class="pp-btn pp-btn-ghost">
                        {{ __('Unseal & Edit') }}
                    </button>
                </form>
            </div>
        @endif
    </x-slot>
    <div>
        <div class="pp-content-wrap">
            @if (session('status') === 'message-sent')
                        <div class="pp-stamp-badge text-sm mb-4">
                            {{ __('Sealed! Your letter will arrive on :day the :date at noon.', [
                    'day' => session('deliveryDayName'),
                    'date' => session('deliveryDayOrdinal'),
                ]) }}
                        </div>
            @endif


            <div class="pp-letter-plain">
                @forelse ($messages as $letter)
                    <div class="pp-letter-entry p-8 sm:p-12">

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
</x-app-layout>