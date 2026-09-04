<x-app-layout>
    <x-slot name="header">
        <h2 class="pp-serif font-semibold text-xl" style="color: var(--ink);">
            {{ __('Settings') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 pp-letter-card">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>
            <div class="p-4 sm:p-8 pp-letter-card">
                <div class="max-w-xl" x-data="{
                    time: '',
                    countdown: '',
                    deliveryCountdown: '',
                    pickupAt: new Date('{{ $nextPickup->toIso8601String() }}').getTime(),
                    deliveryAt: new Date('{{ $nextBatch->toIso8601String() }}').getTime(),
                    tick() {
                        const now = new Date();
                        this.time = now.toLocaleTimeString('en-GB', {
                            timeZone: 'UTC',
                            hour: '2-digit',
                            minute: '2-digit',
                            second: '2-digit',
                        });

                        this.countdown = this.formatRemaining(
                            this.pickupAt - now.getTime(),
                            '{{ __('Pickup has passed — check back after delivery.') }}'
                        );

                        this.deliveryCountdown = this.formatRemaining(
                            this.deliveryAt - now.getTime(),
                            '{{ __('Delivering now…') }}'
                        );
                    },
                    formatRemaining(remaining, passedLabel) {
                        if (remaining <= 0) {
                            return passedLabel;
                        }

                        const days = Math.floor(remaining / (1000 * 60 * 60 * 24));
                        const hours = Math.floor((remaining / (1000 * 60 * 60)) % 24);
                        const minutes = Math.floor((remaining / (1000 * 60)) % 60);
                        const seconds = Math.floor((remaining / 1000) % 60);

                        return days + 'd ' + String(hours).padStart(2, '0') + 'h '
                            + String(minutes).padStart(2, '0') + 'm '
                            + String(seconds).padStart(2, '0') + 's';
                    },
                    init() {
                        this.tick();
                        setInterval(() => this.tick(), 1000);
                    }
                }">
                    <h2 class="text-lg pp-serif font-medium" style="color: var(--ink);">
                        {{ __('Current GMT time') }}
                    </h2>
                    <p class="mt-1 text-sm" style="color: var(--ink-soft);">
                        {{ __('Penny Post delivery times are always shown in GMT. This is the current time in that zone.') }}
                    </p>
                    <p class="pp-mono mt-2" style="color: var(--ink); font-size: 1.5rem;" x-text="time"></p>

                    <div class="mt-6 pt-6" style="border-top: 1px solid var(--line);">
                        <h3 class="text-sm pp-serif font-medium" style="color: var(--ink);">
                            {{ __('Next pickup') }}
                        </h3>
                        <p class="mt-1 text-sm" style="color: var(--ink-soft);">
                            {{ __('Seal a letter before this to catch the next batch.') }}
                        </p>
                        <p class="pp-mono mt-2" style="color: var(--ink); font-size: 1.5rem;" x-text="countdown"></p>
                    </div>

                    <div class="mt-6 pt-6" style="border-top: 1px solid var(--line);">
                        <h3 class="text-sm pp-serif font-medium" style="color: var(--ink);">
                            {{ __('Next batch delivered') }}
                        </h3>
                        <p class="mt-1 pp-serif" style="color: var(--ink);">
                            {{ __('Friday :date at noon GMT.', ['date' => $nextBatch->format('j F')]) }}
                        </p>
                        <p class="pp-mono mt-2" style="color: var(--ink); font-size: 1.5rem;" x-text="deliveryCountdown"></p>
                    </div>
                </div>
            </div>
            <div class="p-4 sm:p-8 pp-letter-card">
                <div class="max-w-xl">
                    <h2 class="text-lg pp-serif font-medium" style="color: var(--ink);">
                        {{ __('Download your data') }}
                    </h2>
                    <p class="mt-1 text-sm" style="color: var(--ink-soft);">
                        {{ __('Get a copy of your account information and every letter you\'ve sent, received, or drafted, in a portable JSON file.') }}
                    </p>
                    <a href="{{ route('profile.export') }}" class="pp-btn pp-btn-ghost mt-4 inline-flex">
                        {{ __('Download my data') }}
                    </a>
                </div>
            </div>

            <div class="p-4 sm:p-8 pp-letter-card">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
