<x-app-layout>
    <x-slot name="header">
        <div style="display: grid; grid-template-columns: 1fr auto; align-items: center; gap: 16px;">
            <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
                {{ $letter->exists ? __('Edit your letter') : __('Write a letter') }}
            </h2>

            <div class="flex items-center gap-3">
                <button type="submit" name="intent" value="draft" form="letter-form" class="pp-btn pp-btn-ghost">
                    {{ __('Save draft') }}
                </button>
                <button type="submit" name="intent" value="send" form="letter-form" class="pp-btn pp-btn-solid"
                    style="padding: 11px 28px; font-size: 15px;">
                    {{ __('Seal & send') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="pp-content-wrap">
            @if (session('status') === 'draft-saved')
                        @php
                            $cutoffAt = $nextBatch->copy()->subDays(config('pennypost.cutoff_days_before_batch'));
                        @endphp
                        <div class="pp-stamp-badge text-sm mb-4">
                            {{ __('Draft saved. Seal & send before :cutoff if you want your mail to arrive :delivery.', [
                    'cutoff' => \App\Models\Message::humanDayLabel($cutoffAt) . ' at ' . $cutoffAt->format('H:i'),
                    'delivery' => \App\Models\Message::humanDayLabel($nextBatch),
                ]) }}
                        </div>
            @elseif (session('status') === 'message-unsealed')
                <div class="pp-stamp-badge text-sm mb-4">{{ __('Unsealed — this is a draft again.') }}</div>
            @endif

            <div class="pp-letter-plain p-8 sm:p-12">
                <form id="letter-form" method="POST"
                    action="{{ $letter->exists ? route('messages.update', $letter) : route('messages.store') }}" x-data="{
            query: @js(old('recipient_name', $letter->recipient->name ?? request('to_name', ''))),
            recipientId: @js(old('recipient_id', $letter->recipient_id ?? request('to_id', ''))),
            lastSelectedQuery: @js(old('recipient_name', $letter->recipient->name ?? request('to_name', ''))),
            results: [],
            open: false,
            search() {
                if (this.query !== this.lastSelectedQuery) {
                    this.recipientId = '';
                }

                if (this.query.length < 1) { this.results = []; this.open = false; return; }
                fetch(`{{ route('users.search') }}?q=${encodeURIComponent(this.query)}`)
                    .then(r => r.json())
                    .then(data => { this.results = data; this.open = data.length > 0; });
            },
            select(user) {
                this.recipientId = user.id;
                this.query = user.name;
                this.lastSelectedQuery = user.name;
                this.open = false;
                this.results = [];
            },
        }">
                    @csrf
                    @if ($letter->exists)
                        @method('PUT')
                    @endif

                    <div class="text-right">
                        <p class="pp-serif" style="color: var(--ink-soft);">
                            {{ now()->format('j F Y') }}
                        </p>
                    </div>

                    <div class="relative mt-6 mx-auto" style="width: fit-content;">
                        <div class="inline-flex items-baseline">
                            <label for="recipient_name" class="pp-serif shrink-0 italic"
                                style="color: var(--ink); font-size: 1.25rem; line-height: 1.75; margin-right: 0.75rem;">{{ __('To') }}</label>
                            <input id="recipient_name" type="text" autocomplete="off"
                                class="pp-input-line pp-input-line--flush pp-serif uppercase" style="width: 14rem;"
                                x-model="query" @input="search()">
                        </div>
                        <input type="hidden" name="recipient_id" x-model="recipientId">

                        <ul x-show="open" x-cloak @click.outside="open = false"
                            class="absolute z-10 mt-1 w-64 bg-[var(--paper-card)] border border-[var(--line)] shadow-lg divide-y divide-[var(--line)]">
                            <template x-for="user in results" :key="user.id">
                                <li @click="select(user)"
                                    class="px-4 py-2 text-sm text-[var(--ink)] hover:bg-[var(--paper)] cursor-pointer"
                                    x-text="user.name"></li>
                            </template>
                        </ul>

                        @error('recipient_id')
                            <p class="mt-2 text-sm text-[var(--error-red)]">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="mt-8">
                        <label for="body" class="sr-only">{{ __('Your letter') }}</label>
                        <textarea id="body" name="body" rows="10"
                            maxlength="{{ config('pennypost.max_letter_length') }}"
                            class="pp-textarea-plain pp-serif mt-3 text-[1.25rem] leading-[1.75]">{{ old('body', $letter->body ?? '') }}</textarea>
                        @error('body')
                            <p class="mt-2 text-sm text-[var(--error-red)]">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mt-8 text-right">
                        <p class="pp-serif" style="color: var(--ink); font-size: 1.25rem; line-height: 1.75;">
                            [{{ auth()->user()->name }}]
                        </p>
                    </div>
                </form>
            </div>

            @if ($letter->exists && $letter->is_draft)
                <form method="POST" action="{{ route('messages.destroy', $letter) }}"
                    onsubmit="return confirm('{{ __('Delete this draft for good?') }}')" class="mt-4 text-right">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                        class="text-xs text-[var(--ink-soft)] hover:text-[var(--error-red)] pp-mono underline">
                        {{ __('Delete this draft') }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</x-app-layout>