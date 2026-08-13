<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Inbox') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @forelse ($messages as $message)
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="flex items-baseline justify-between">
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $message->sender->name }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ $message->delivered_at->format('j M Y, H:i') }} GMT
                        </p>
                    </div>
                    <p class="mt-2 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $message->body }}</p>
                </div>
            @empty
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg text-gray-600 dark:text-gray-400 text-sm">
                    {{ __('Nothing here yet. Delivered messages show up here every Sunday at noon GMT.') }}
                </div>
            @endforelse

            {{ $messages->links() }}
        </div>
    </div>
</x-app-layout>
