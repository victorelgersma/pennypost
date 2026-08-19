<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl text-[var(--ink)] leading-tight">
            {{ __('Sent') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @if (session('status') === 'message-sent')
                <div class="pp-stamp-badge text-sm">
                    {{ __('Message sealed! It will be delivered with the next batch.') }}
                </div>
            @endif

            @forelse ($messages as $message)
                <div class="pp-letter-card p-4 sm:p-6">
                    <div class="flex items-baseline justify-between gap-4 flex-wrap">
                        <p class="pp-serif font-medium text-[var(--ink)]">
                            {{ __('To') }} {{ $message->recipient->name }}
                        </p>
                        @if ($message->isDelivered())
                            <span class="pp-mono text-xs px-2 py-1 border border-[var(--line)] text-[var(--ink-soft)]">
                                {{ __('Delivered') }} {{ $message->delivered_at->format('j M, H:i') }} GMT
                            </span>
                        @else
                            <span class="pp-mono text-xs px-2 py-1 border border-[var(--stamp-red)] text-[var(--stamp-red)]">
                                {{ __('Sent on') }} {{ $message->scheduled_for->format('j M, H:i') }} GMT
                            </span>
                            <span class="pp-mono text-xs px-2 py-1 border border-[var(--stamp-red)] text-[var(--stamp-red)]">
                                {{ __('Expected delivery') }}
                                {{ $message->scheduled_for->copy()->addDays(4)->format('j M, H:i') }} GMT
                            </span>
                        @endif
                    </div>
                    <p class="mt-2 text-[var(--ink)] whitespace-pre-wrap">{{ $message->body }}</p>

                    @if ($message->canUnseal())
                        <form method="POST" action="{{ route('messages.unseal', $message) }}"
                            class="mt-4 pt-4 border-t border-dashed border-[var(--line)] flex items-center justify-between gap-4 flex-wrap">
                            @csrf
                            <p class="text-xs text-[var(--ink-soft)] pp-mono">
                                {{ __('Unseal until') }} {{ $message->unsealDeadline()->format('D, j M \a\t H:i') }} GMT
                                {{ __('to keep editing.') }}
                            </p>
                            <button type="submit" class="pp-btn pp-btn-ghost">
                                {{ __('Unseal') }}
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="pp-letter-card p-6 text-[var(--ink-soft)] text-sm">
                    {{ __("You haven't sent any letters yet.") }}
                </div>
            @endforelse

            {{ $messages->links() }}
        </div>
    </div>
</x-app-layout>