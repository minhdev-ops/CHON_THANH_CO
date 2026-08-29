@props([
    'locale',
    'label',
    'fields',
    'values' => [],
])

@php
    $isVi = $locale === 'vi';
    $id = $isVi ? 'vi-tab' : 'en-tab';
@endphp

<div x-show="lang === '{{ $locale }}'" x-cloak>
    <div class="rounded border border-gray-200 bg-gray-50 p-4 mb-6">
        <p class="text-xs text-gray-500 mb-4">{{ $isVi ? 'Nội dung hiển thị cho khách hàng Việt Nam.' : 'Nội dung hiển thị cho khách hàng nước ngoài. Để trống nếu chưa cần.' }}</p>

        @foreach ($fields as $field)
            @php
                $name = 'translations.' . $locale . '.' . $field['name'];
                $value = $values[$field['name']] ?? '';
            @endphp

            <x-admin.partials.input
                :name="$name"
                :label="$field['label']"
                :type="$field['type'] ?? 'text'"
                :value="$value"
                :required="($field['required'] ?? false) && $isVi"
                :max="$field['max'] ?? null"
                :rows="$field['rows'] ?? 4"
                :hint="$field['hint'] ?? null"
            />
        @endforeach
    </div>
</div>
