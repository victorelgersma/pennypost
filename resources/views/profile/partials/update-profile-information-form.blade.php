<section>
    <header>
        <h2 class="text-lg pp-serif font-medium" style="color: var(--ink);">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm" style="color: var(--ink-soft);">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />
        </div>

        <div x-data="{ enabled: {{ old('username', $user->username) ? 'true' : 'false' }} }">
            <label class="inline-flex items-center gap-2" style="cursor: pointer;">
                <input type="checkbox" x-model="enabled" class="pp-checkbox"
                    @change="if (!enabled) { $refs.usernameInput.value = '' }">
                <span class="pp-field-label" style="display: inline; text-transform: none; letter-spacing: normal; font-weight: 500; color: var(--ink);">
                    {{ __('Enable public profile') }}
                </span>
            </label>
            <p class="mt-1 text-xs" style="color: var(--ink-soft);">
                {{ __('A shareable page with your name and a way for anyone to write to you — handy for a bio link. Off by default.') }}
            </p>

            <div x-show="enabled" x-cloak class="mt-3">
                <x-input-label for="username" :value="__('Username')" />
                <x-text-input id="username" name="username" type="text" class="mt-1 block w-full" x-ref="usernameInput"
                    :value="old('username', $user->username)" autocomplete="off" placeholder="{{ __('e.g. victor') }}" />
                <p class="mt-1 text-xs" style="color: var(--ink-soft);">
                    {{ __('Lowercase letters, numbers, hyphens and underscores only.') }}
                </p>
                <x-input-error class="mt-2" :messages="$errors->get('username')" />


@if ($user->username)
    <div class="mt-3 flex items-center gap-3 flex-wrap" x-data="{
            copied: false,
            url: @js(config('pennypost.short_profile_domain') ? rtrim(config('pennypost.short_profile_domain'), '/').'/'.$user->username : route('profile.public', $user->username)),
            copy() {
                navigator.clipboard.writeText(this.url).then(() => {
                    this.copied = true;
                    setTimeout(() => this.copied = false, 2000);
                });
            },
        }">
        <button type="button" @click="copy()" class="pp-btn pp-btn-ghost" style="padding: 6px 14px; font-size: 13px;">
            <span x-show="!copied">{{ __('Copy public profile') }}</span>
            <span x-show="copied" x-cloak>{{ __('Copied!') }}</span>
        </button>
        <a href="{{ route('profile.public', $user->username) }}" target="_blank" rel="noopener"
            class="pp-btn pp-btn-ghost" style="padding: 6px 14px; font-size: 13px;">
            {{ __('View public profile') }}
        </a>
    </div>
@endif

            </div>
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm"
                    style="color: var(--ink-soft);"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
