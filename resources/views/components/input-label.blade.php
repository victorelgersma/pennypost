@props(['value'])

<label {{ $attributes->merge(['class' => 'pp-field-label mb-1']) }}>
    {{ $value ?? $slot }}
</label>