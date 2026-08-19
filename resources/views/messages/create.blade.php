<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl text-[var(--ink)] leading-tight">
            {{ $letter->exists ? __('Edit your letter') : __('Write a letter') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'draft-saved')
                <div class="pp-stamp-badge text-sm">{{ __('Draft saved. Come back whenever.') }}</div>
            @elseif (session('status') === 'message-unsealed')
                <div class="pp-stamp-badge text-sm">{{ __('Unsealed — this is a draft again.') }}</div>
            @endif
            

            <form method="POST"
                action="{{ $letter->exists ? route('messages.update', $letter) : route('messages.store') }}" x-data="{
                        query: '{{ old('recipient_name', $letter->recipient->name ?? request('to_name', '')) }}',
                        recipientId: '{{ old('recipient_id', $letter->recipient_id ?? request('to_id', '')) }}',
                        results: [],
                        open: false,
                        search() {
                            if (this.query.length < 1) { this.results = []; this.open = false; return; }
                            fetch(`{{ route('users.search') }}?q=${encodeURIComponent(this.query)}`)
                                .then(r => r.json())
                                .then(data => { this.results = data; this.open = data.length > 0; });
                        },
                        select(user) {
                            this.recipientId = user.id;
                            this.query = user.name;
                            this.open = false;
                            this.results = [];
                        },
                    }">
                @csrf
                @if ($letter->exists)
                    @method('PUT')
                @endif

                <div class="relative">
                    <label for="recipient_name" class="pp-field-label">{{ __('To') }}</label>
                    <input id="recipient_name" type="text" autocomplete="off" class="pp-input-line pp-serif mt-1"
                        x-model="query" @input="search()" placeholder="{{ __('Who is this for?') }}">
                    <input type="hidden" name="recipient_id" x-model="recipientId">

                    <ul x-show="open" x-cloak @click.outside="open = false"
                        class="absolute z-10 mt-1 w-full bg-[var(--paper-card)] border border-[var(--line)] shadow-lg divide-y divide-[var(--line)]">
                        <template x-for="user in results" :key="user.id">
                            <li @click="select(user)"
                                class="px-4 py-2 text-sm text-[var(--ink)] hover:bg-[var(--paper)] cursor-pointer"
                                x-text="user.name"></li>
                        </template>
                    </ul>

                    @error('recipient_id')
                        <p class="mt-2 text-sm text-[var(--stamp-red)]">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mt-8">
                    <label for="body" class="pp-field-label">{{ __('Your letter') }}</label>
                    <textarea id="body" name="body" rows="10" maxlength="2000"
                        class="pp-textarea-ruled pp-serif mt-3 text-[17px]">{{ old('body', $letter->body ?? '') }}</textarea>
                    @error('body')
                        <p class="mt-2 text-sm text-[var(--stamp-red)]">{{ $message }}</p>
                    @enderror
                </div>

                <div
                    class="flex items-center justify-between mt-8 pt-6 border-t border-dashed border-[var(--line)] flex-wrap gap-4">
                    <p class="text-xs text-[var(--ink-soft)] pp-mono max-w-xs">
    {{ __('Sending seals it into the next batch, which will be posted on :post_date and delivered on :delivery_date.', [
        'post_date' => $nextBatch->copy()->subDays(4)->format('D, j M') . ' at noon',
        'delivery_date' => $nextBatch->format('D, j M') . ' at noon',
    ]) }}
</p>
                    <div class="flex items-center gap-3">
                        <button type="submit" name="intent" value="draft" class="pp-btn pp-btn-ghost">
                            {{ __('Save draft') }}
                        </button>
                        <button type="submit" name="intent" value="send" class="pp-btn pp-btn-solid">
                            {{ __('Seal & send') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>

        @if ($letter->exists && $letter->is_draft)
            <form method="POST" action="{{ route('messages.destroy', $letter) }}"
                onsubmit="return confirm('{{ __('Delete this draft for good?') }}')">
                @csrf
                @method('DELETE')
                <button type="submit"
                    class="text-xs text-[var(--ink-soft)] hover:text-[var(--stamp-red)] pp-mono underline">
                    {{ __('Delete this draft') }}
                </button>
            </form>
        @endif
    </div>
    </div>
</x-app-layout>