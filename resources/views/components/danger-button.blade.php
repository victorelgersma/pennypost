<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pp-btn pp-btn-danger']) }}>
    {{ $slot }}
</button>