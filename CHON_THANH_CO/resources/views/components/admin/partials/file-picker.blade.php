@props([
    'name',
    'label',
    'value' => '',
    'required' => false,
    'hint' => null,
    'folder' => null,
    'fieldName' => null,
])

@php
    $error = $errors->first($name);
    $inputId = str_replace(['.', '*', '[]', '[', ']'], ['_', '_', '', '_', ''], $name);
    if ($fieldName) {
        $resolvedName = $fieldName;
    } else {
        $nameParts = explode('.', $name);
        $resolvedName = array_shift($nameParts) . ($nameParts ? '[' . implode('][', $nameParts) . ']' : '');
    }
    $classes = 'w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 ' . ($error ? 'border-red-400' : '');
@endphp

<div class="mb-4">
    <label for="{{ $inputId }}" class="block text-sm font-semibold mb-1.5">
        {{ $label }}
        @if ($required)
            <span class="text-red-500">*</span>
        @endif
    </label>

    <div class="flex items-start gap-3">
        <input id="{{ $inputId }}" name="{{ $resolvedName }}" type="text"
            value="{{ old($name, $value) }}" placeholder="/userfiles/files/..."
            {{ $required ? 'required' : '' }} class="{{ $classes }} flex-1">

        <button type="button" data-finder="{{ $inputId }}" data-type="Files"
            @if ($folder) data-folder="{{ $folder }}" @endif
            class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded whitespace-nowrap flex items-center gap-1.5">
            <span class="material-symbols-outlined text-[18px]">upload_file</span>
            Chọn file
        </button>
    </div>
    @if ($folder)
        <p class="text-xs text-gray-400 mt-1">File sẽ lưu vào thư mục <code class="text-gray-600">/userfiles/files/{{ $folder }}/</code></p>
    @endif

    @if ($hint)
        <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
    @endif
    @if ($error)
        <p class="text-xs text-red-600 mt-1">{{ $error }}</p>
    @endif

    <div class="mt-2" id="{{ $inputId }}_preview">
        @if ($value)
            <div class="flex items-center gap-2 text-blue-600 text-sm">
                <span class="material-symbols-outlined text-[18px]">description</span>
                <a href="{{ $value }}" target="_blank" class="underline break-all">{{ $value }}</a>
            </div>
        @endif
    </div>
</div>