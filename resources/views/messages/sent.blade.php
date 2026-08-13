<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sent') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'message-sent')
                <div class="p-4 bg-green-50 dark:bg-green-900/40 text-green-700 dark:text-green-300 rounded-lg text-sm">
                    {{ __('Message sent! It will be delivered with the next batch.') }}
                </div>
            @endif

            @forelse ($messages as $message)
                <div class="p-4 sm:p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg">
                    <div class="flex items-baseline justify-between">
                        <p class="font-medium text-gray-900 dark:text-gray-100">
                            {{ __('To') }} {{ $message->recipient->name }}
                        </p>
                        @if ($message->isDelivered())
                            <span class="text-xs font-medium px-2 py-1 rounded-full bg-green-100 dark:bg-green-900/40 text-green-700 dark:text-green-300">
                                {{ __('Delivered') }} {{ $message->delivered_at->format('j M, H:i') }} GMT
                            </span>
                        @else
                            <span class="text-xs font-medium px-2 py-1 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300">
                                {{ __('Scheduled for') }} {{ $message->scheduled_for->format('j M, H:i') }} GMT
                            </span>
                        @endif
                    </div>
                    <p class="mt-2 text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $message->body }}</p>
                </div>
            @empty
                <div class="p-6 bg-white dark:bg-gray-800 shadow-sm rounded-lg text-gray-600 dark:text-gray-400 text-sm">
                    {{ __("You haven't sent any messages yet.") }}
                </div>
            @endforelse

            {{ $messages->links() }}
        </div>
    </div>
</x-app-layout>
