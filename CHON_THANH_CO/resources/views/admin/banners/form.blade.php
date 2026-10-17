@extends('admin.layouts.app')

@section('title', 'Banner & Hero')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $banner ? route('admin.banners.update', $banner) : route('admin.banners.store') }}"
            x-data="{ section: '{{ old('section', $banner?->section ?? 'hero') }}' }">
            @csrf
            @if ($banner)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="section" class="block text-sm font-semibold mb-1.5">Vị trí <span class="text-red-500">*</span></label>
                        <select id="section" name="section" x-model="section"
                            class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach ($sectionLabels as $value => $label)
                                <option value="{{ $value }}" {{ old('section', $banner?->section) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$banner?->sort_order" min="0" />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-2">
                    <x-admin.partials.input name="link_to" label="Liên kết (link_to)" :value="$banner?->link_to" :hint="'VD: /contact hoặc URL ngoài.'" />
                    <div class="pt-7">
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$banner?->is_active ?? true" />
                    </div>
                </div>

                <x-admin.partials.image-picker name="image" label="Ảnh nền" :value="$banner?->image" :hint="'Chỉ dùng cho hero trang chủ. Để trống nếu không cần ảnh.'" folder="banners" />

                <x-admin.partials.input name="title" label="Tiêu đề" :value="$banner?->title" max="300" />
                <x-admin.partials.input name="subtitle" label="Phụ đề (hero)" :value="$banner?->subtitle" max="500" />
                <x-admin.partials.input name="text" label="Mô tả (CTA)" :value="$banner?->text" type="textarea" rows="3" />
                <x-admin.partials.input name="button_text" label="Chữ trên nút" :value="$banner?->button_text" max="100" />
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $banner ? 'Lưu thay đổi' : 'Tạo banner' }}
                </button>
                <a href="{{ route('admin.banners.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection