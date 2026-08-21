<section class="space-y-6">
    <header>
        <h2 class="text-lg pp-serif font-medium" style="color: var(--ink);">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm" style="color: var(--ink-soft);">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information you wish to retain.') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.destroy') }}"
        onsubmit="return confirm('{{ __('Are you sure you want to delete your account? This cannot be undone.') }}')">
        @csrf
        @method('delete')

        <x-danger-button>{{ __('Delete Account') }}</x-danger-button>
    </form>
</section>