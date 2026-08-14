@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'pp-stamp-badge text-sm']) }}>
        {{ $status }}
    </div>
@endif