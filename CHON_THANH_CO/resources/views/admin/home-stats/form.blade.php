@extends('admin.layouts.app')

@section('title', 'Số liệu năng lực')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $stat ? route('admin.home-stats.update', $stat) : route('admin.home-stats.store') }}"
            x-data="{ lang: 'vi' }">
            @csrf
            @if ($stat)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <div class="grid grid-cols-3 gap-4">
                    <x-admin.partials.input name="icon" label="Icon" :value="$stat?->icon" :hint="'Tên Material Symbol, VD: handshake, verified...'" required />
                    <x-admin.partials.input name="value" label="Giá trị" :value="$stat?->value" :hint="'VD: 20+, 120'" required />
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$stat?->sort_order" min="0" />
                </div>

                <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$stat?->is_active ?? true" />
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
                        ['name' => 'label', 'label' => 'Nhãn', 'required' => true, 'max' => 150],
                    ]"
                    :values="$stat?->translation('vi')?->toArray() ?? []"
                />

                <x-admin.partials.translation-section
                    locale="en"
                    label="English"
                    :fields="[
                        ['name' => 'label', 'label' => 'Label', 'max' => 150],
                    ]"
                    :values="$stat?->translation('en')?->toArray() ?? []"
                />
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $stat ? 'Lưu thay đổi' : 'Tạo số liệu' }}
                </button>
                <a href="{{ route('admin.home-stats.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
