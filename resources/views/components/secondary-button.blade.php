<button {{ $attributes->merge(['type' => 'button', 'class' => 'pp-btn pp-btn-ghost']) }}>
    {{ $slot }}
</button>