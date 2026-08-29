@extends('admin.layouts.app')

@section('title', 'Câu hỏi thường gặp')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $faq ? route('admin.faqs.update', $faq) : route('admin.faqs.store') }}"
            x-data="{ lang: 'vi' }">
            @csrf
            @if ($faq)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$faq?->sort_order" min="0" />
                    <div class="pt-7">
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$faq?->is_active ?? true" />
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
                        ['name' => 'question', 'label' => 'Câu hỏi', 'required' => true, 'max' => 500],
                        ['name' => 'answer', 'label' => 'Trả lời', 'type' => 'textarea', 'required' => true, 'rows' => 6],
                    ]"
                    :values="$faq?->translation('vi')?->toArray() ?? []"
                />

                <x-admin.partials.translation-section
                    locale="en"
                    label="English"
                    :fields="[
                        ['name' => 'question', 'label' => 'Question', 'max' => 500],
                        ['name' => 'answer', 'label' => 'Answer', 'type' => 'textarea', 'rows' => 6],
                    ]"
                    :values="$faq?->translation('en')?->toArray() ?? []"
                />
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $faq ? 'Lưu thay đổi' : 'Tạo FAQ' }}
                </button>
                <a href="{{ route('admin.faqs.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
