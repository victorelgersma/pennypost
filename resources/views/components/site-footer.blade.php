<footer class="pp-footer">
    <div class="pp-wrap pp-footer-inner">
        <p class="pp-footer-text">
            &copy; {{ now()->year }} Penny Post
        </p>
        <div class="pp-footer-links">
            <a href="{{ route('about') }}">{{ __('About') }}</a>
            <a href="{{ route('feedback.create') }}">{{ __('Feedback') }}</a>
            <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a>
            <a href="mailto:pennypost@vjbe.net">{{ __('Contact') }}</a>
            <a href="https://github.com/victorelgersma/pennypost" target="_blank" rel="noopener"
                aria-label="{{ __('View source on GitHub') }}" style="display: inline-flex; align-items: center;">
                <x-icons.github size="16" />
            </a>
        </div>
    </div>
</footer>
