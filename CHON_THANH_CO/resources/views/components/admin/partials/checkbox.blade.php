@props(['name', 'label', 'checked' => false])

<div class="flex items-center gap-3 mb-4">
    <input id="{{ str_replace(['.', '*'], ['_', '_'], $name) }}" name="{{ $name }}" type="checkbox" value="1"
        {{ old($name, $checked) ? 'checked' : '' }}
        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
    <label for="{{ str_replace(['.', '*'], ['_', '_'], $name) }}" class="text-sm font-medium">{{ $label }}</label>
</div>
