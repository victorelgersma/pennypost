<footer class="pp-footer">
    <div class="pp-wrap pp-footer-inner">
        <p class="pp-footer-text">
            &copy; {{ now()->year }} Penny Post
        </p>
        <div class="pp-footer-links">
            <a href="{{ route('about') }}">{{ __('About') }}</a>
            <a href="{{ route('privacy') }}">{{ __('Privacy Policy') }}</a>
            <a href="mailto:pennypost@vjbe.net">{{ __('Contact') }}</a>
        </div>
    </div>
</footer>