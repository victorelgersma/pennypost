<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Write a message') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <p class="mb-6 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('Messages are delivered once a week, Sunday at 12:00 GMT. Send before Friday 12:00 GMT to catch the next batch.') }}
                    {{ __('Right now, that\'s') }}
                    <span class="font-medium text-gray-800 dark:text-gray-200">{{ $nextBatch->format('l, j M Y \a\t H:i') }} GMT</span>.
                </p>

                <form
                    method="POST"
                    action="{{ route('messages.store') }}"
                    x-data="{
                        query: '{{ old('recipient_name', '') }}',
                        recipientId: '{{ old('recipient_id', '') }}',
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
                    }"
                >
                    @csrf

                    <div class="relative">
                        <x-input-label for="recipient_name" :value="__('To')" />
                        <x-text-input
                            id="recipient_name"
                            class="block mt-1 w-full"
                            type="text"
                            autocomplete="off"
                            x-model="query"
                            @input="search()"
                            placeholder="{{ __('Start typing a name...') }}"
                        />
                        <input type="hidden" name="recipient_id" x-model="recipientId">

                        <ul
                            x-show="open"
                            x-cloak
                            @click.outside="open = false"
                            class="absolute z-10 mt-1 w-full bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-md shadow-lg divide-y divide-gray-100 dark:divide-gray-600"
                        >
                            <template x-for="user in results" :key="user.id">
                                <li
                                    @click="select(user)"
                                    class="px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-600 cursor-pointer"
                                    x-text="user.name"
                                ></li>
                            </template>
                        </ul>

                        <x-input-error :messages="$errors->get('recipient_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="body" :value="__('Message')" />
                        <textarea
                            id="body"
                            name="body"
                            rows="8"
                            maxlength="2000"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm block mt-1 w-full"
                        >{{ old('body') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end mt-6">
                        <x-primary-button>{{ __('Send') }}</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
