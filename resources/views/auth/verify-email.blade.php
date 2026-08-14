<x-guest-layout>
    <p class="text-sm mb-4" style="color: var(--ink-soft);">
        {{ __('Thanks for signing up! Before getting started, verify your email address by clicking the link we just sent you. If it didn\'t arrive, we can send another.') }}
    </p>

    @if (session('status') == 'verification-link-sent')
        <div class="pp-stamp-badge text-sm mb-4">
            {{ __('A new verification link has been sent to your email address.') }}
        </div>
    @endif

    <div class="flex items-center justify-between">
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <x-primary-button>
                {{ __('Resend Verification Email') }}
            </x-primary-button>
        </form>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="pp-link text-sm">
                {{ __('Log Out') }}
            </button>
        </form>
    </div>
</x-guest-layout>