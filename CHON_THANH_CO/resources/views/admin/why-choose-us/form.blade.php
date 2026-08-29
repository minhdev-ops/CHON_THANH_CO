@extends('admin.layouts.app')

@section('title', 'Lý do chọn')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $item ? route('admin.why-choose-us.update', $item) : route('admin.why-choose-us.store') }}"
            x-data="{ lang: 'vi' }">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="icon" label="Icon" :value="$item?->icon" :hint="'Tên Material Symbol, VD: handshake, verified...'" required />
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$item?->sort_order" min="0" />
                </div>

                <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$item?->is_active ?? true" />
            </div>

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <div class="flex gap-2 mb-4">
                    <button type="button" @click="lang = 'vi'" :class="lang === 'vi' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-4 py-1.5 rounded text-sm font-semibold">Tiếng Việt</button>
                    <button type="button" @click="lang = 'en'" :class="lang === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-4 py-1.5 rounded text-sm font-semibold">English</button>
                </div>

                <x-admin.partials.translation-section
                    locale="vi"
                    label="Tiếng Việt"
                    :fields="[
                        ['name' => 'title', 'label' => 'Tiêu đề', 'required' => true, 'max' => 200],
                        ['name' => 'description', 'label' => 'Mô tả', 'type' => 'textarea', 'required' => true],
                    ]"
                    :values="$item?->translation('vi')?->toArray() ?? []"
                />

                <x-admin.partials.translation-section
                    locale="en"
                    label="English"
                    :fields="[
                        ['name' => 'title', 'label' => 'Title', 'max' => 200],
                        ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                    ]"
                    :values="$item?->translation('en')?->toArray() ?? []"
                />
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $item ? 'Lưu thay đổi' : 'Tạo mục' }}
                </button>
                <a href="{{ route('admin.why-choose-us.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
