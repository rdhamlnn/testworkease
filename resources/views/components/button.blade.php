<!-- resources/views/components/button.blade.php -->
@props([
    'type' => 'button',
    'class' => '',
    'icon' => '',
    'label' => '',
    'id' => null,
    'title' => null,
    'data' => []
])
<button
    type="{{ $type }}"
    @if($id) id="{{ $id }}" @endif
    @if($title) title="{{ $title }}" @endif
    {{ $attributes->merge(['class' => 'btn ' . $class]) }}
    @foreach($data as $key => $value)
        {{ $key }}="{{ $value }}"
    @endforeach
>
    @if($icon)
        <i class="{{ $icon }}"></i>
    @endif
    {{ $label }}
</button>
