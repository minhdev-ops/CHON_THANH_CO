@extends('admin.layouts.app')

@section('title', 'Chứng chỉ')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $certificate ? route('admin.certificates.update', $certificate) : route('admin.certificates.store') }}"
            x-data="{ lang: 'vi' }">
            @csrf
            @if ($certificate)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <x-admin.partials.image-picker name="image" label="Ảnh chứng chỉ" :value="$certificate?->image" :hint="'Bấm nút Chọn ảnh để upload hoặc chọn từ thư viện.'" folder="certificates" required />

                <x-admin.partials.file-picker name="file" label="File chứng chỉ (PDF)" :value="$certificate?->file" :hint="'Upload PDF chứng chỉ để khách xem trực tiếp và tải về trên website. Không bắt buộc.'" folder="certificates" />

                @if ($certificate?->image)
                    <img src="{{ $certificate->image }}" alt="" class="h-20 w-auto object-contain mb-4 border border-gray-200 rounded">
                @endif

                <x-admin.partials.input name="slug" label="Slug" :value="$certificate?->slug" :hint="'Để trống để tự tạo từ tên tiếng Việt.'" />

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$certificate?->sort_order" min="0" />
                    <div class="pt-7">
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$certificate?->is_active ?? true" />
                    </div>
                </div>
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
                        ['name' => 'name', 'label' => 'Tên chứng chỉ', 'required' => true, 'max' => 200],
                        ['name' => 'description', 'label' => 'Mô tả', 'type' => 'textarea'],
                    ]"
                    :values="$certificate?->translation('vi')?->toArray() ?? []"
                />

                <x-admin.partials.translation-section
                    locale="en"
                    label="English"
                    :fields="[
                        ['name' => 'name', 'label' => 'Name', 'max' => 200],
                        ['name' => 'description', 'label' => 'Description', 'type' => 'textarea'],
                    ]"
                    :values="$certificate?->translation('en')?->toArray() ?? []"
                />
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $certificate ? 'Lưu thay đổi' : 'Tạo chứng chỉ' }}
                </button>
                <a href="{{ route('admin.certificates.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
