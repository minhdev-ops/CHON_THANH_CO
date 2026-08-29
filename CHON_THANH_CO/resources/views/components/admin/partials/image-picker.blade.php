@props([
    'name',
    'label',
    'value' => '',
    'required' => false,
    'hint' => null,
    'resourceType' => 'Images',
    'folder' => null,
    'preview' => true,
])

@php
    $error = $errors->first($name);
    $id = 'picker_' . str_replace(['.', '*', '[]', '[', ']'], ['_', '_', '', '_', ''], $name);
    $nameParts = explode('.', $name);
    $fieldName = array_shift($nameParts) . ($nameParts ? '[' . implode('][', $nameParts) . ']' : '');
    $classes = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ' . ($error ? 'border-red-400' : '');
    $inputId = str_replace(['.', '*', '[]', '[', ']'], ['_', '_', '', '_', ''], $name);
@endphp

<div class="mb-4">
    <label for="{{ $inputId }}" class="block text-sm font-semibold mb-1.5">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <div class="flex items-start gap-3">
        <input id="{{ $inputId }}" name="{{ $fieldName }}" type="text"
            value="{{ old($name, $value) }}" placeholder="/userfiles/images/..."
            {{ $required ? 'required' : '' }} class="{{ $classes }} flex-1">

        <button type="button" data-finder="{{ $inputId }}" data-type="{{ $resourceType }}" @if ($folder) data-folder="{{ $folder }}" @endif
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded whitespace-nowrap flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">image</span>
            Chọn ảnh
        </button>
        @if ($folder)
            <p class="text-xs text-gray-400 mt-1">Ảnh sẽ lưu vào thư mục <code class="text-gray-600">/userfiles/{{ $resourceType === 'Files' ? 'files' : 'images' }}/{{ $folder }}/</code></p>
        @endif
    </div>

    @if ($hint)
        <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="text-xs text-red-600 mt-1">{{ $error }}</p>
    @endif

    @if ($preview)
        <div class="mt-2" id="{{ $inputId }}_preview">
            @if ($value)
                <img src="{{ $value }}" alt="" class="h-20 w-auto object-contain border border-gray-200 rounded">
            @endif
        </div>
    @endif
</div>
