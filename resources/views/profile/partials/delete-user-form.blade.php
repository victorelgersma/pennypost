<section class="space-y-6">
    <header>
        <h2 class="text-lg pp-serif font-medium" style="color: var(--ink);">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm" style="color: var(--ink-soft);">
            {{ __("We'll send a confirmation link to your email before anything is deleted. Once your account is deleted, all of its letters and drafts are gone for good.") }}
        </p>
    </header>

    @if (session('status') === 'account-deletion-requested')
        <div class="pp-stamp-badge text-sm">
            {{ __("Check your email — click the link to confirm deletion. If you didn't mean to do this, just ignore it.") }}
        </div>
    @endif

    <form method="post" action="{{ route('profile.destroy') }}">
        @csrf
        @method('delete')

        <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
    </form>
</section>