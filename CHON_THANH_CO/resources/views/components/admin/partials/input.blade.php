@props([
    'name',
    'label',
    'type' => 'text',
    'value' => '',
    'placeholder' => null,
    'required' => false,
    'max' => null,
    'min' => null,
    'step' => null,
    'rows' => 4,
    'hint' => null,
])

@php
    $error = $errors->first($name);
    $id = str_replace(['.', '*'], ['_', '_'], $name);
    $nameParts = explode('.', $name);
    $fieldName = array_shift($nameParts) . ($nameParts ? '[' . implode('][', $nameParts) . ']' : '');
    $classes = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ' . ($error ? 'border-red-400' : '');
@endphp

<div class="mb-4">
    <label for="{{ $id }}" class="block text-sm font-semibold mb-1.5">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $fieldName }}" rows="{{ $rows }}"
            placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }} class="{{ $classes }}">{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $fieldName }}" type="{{ $type }}"
            value="{{ old($name, $value) }}" placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}
            @if ($max) maxlength="{{ $max }}" @endif
            @if ($min !== null) min="{{ $min }}" @endif
            @if ($max !== null && $type === 'number') max="{{ $max }}" @endif
            @if ($step) step="{{ $step }}" @endif
            class="{{ $classes }}">
    @endif

    @if ($hint)
        <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="text-xs text-red-600 mt-1">{{ $error }}</p>
    @endif
</div>
