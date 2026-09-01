@props(['active'])

@php
$classes = 'block w-full ps-3 pe-4 py-2 border-l-4 text-start text-base font-medium transition duration-150 ease-in-out';
$style = ($active ?? false)
            ? 'border-color: var(--accent); color: var(--ink); background: var(--paper);'
            : 'border-color: transparent; color: var(--ink-soft);';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} style="{{ $style }}">
    {{ $slot }}
</a>