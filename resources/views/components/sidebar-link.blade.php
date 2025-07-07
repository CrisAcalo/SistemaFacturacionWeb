@props(['active' => false])

@php
$classes = 'flex items-center w-full px-6 py-3 space-x-4 transition-colors duration-200 ';
$classes .= ($active ?? false)
            ? 'bg-sidebar-active-bg text-sidebar-text-active font-semibold'
            : 'text-sidebar-text hover:bg-sidebar-active-bg/50 hover:text-sidebar-text-hover';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }} wire:navigate>
    {{ $slot }}
</a>
