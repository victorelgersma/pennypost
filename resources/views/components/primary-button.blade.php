<button {{ $attributes->merge(['type' => 'submit', 'class' => 'pp-btn pp-btn-solid']) }}>
    {{ $slot }}
</button>