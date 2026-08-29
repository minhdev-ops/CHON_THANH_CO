@extends('admin.layouts.app')

@section('title', 'Tin tức')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $item ? route('admin.news.update', $item) : route('admin.news.store') }}"
            x-data="{ lang: 'vi' }">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <div class="mb-4">
                    <label for="news_category_id" class="block text-sm font-semibold mb-1.5">Danh mục tin</label>
                    <select id="news_category_id" name="news_category_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Không chọn --</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('news_category_id', $item?->news_category_id) == $category->id)>{{ $category->translation('vi')?->name }}</option>
                        @endforeach
                    </select>
                </div>

                <x-admin.partials.image-picker name="image" label="Ảnh bài viết" :value="$item?->image" :hint="'Bấm nút Chọn ảnh để upload hoặc chọn từ thư viện.'" folder="news" required />

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="published_at" label="Ngày đăng" type="datetime-local" :value="$item?->published_at?->format('Y-m-d\TH:i')" />
                    <x-admin.partials.input name="slug" label="Slug" :value="$item?->slug" :hint="'Để trống để tự tạo từ tiêu đề.'" />
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
                        ['name' => 'title', 'label' => 'Tiêu đề', 'required' => true, 'max' => 250],
                        ['name' => 'excerpt', 'label' => 'Tóm tắt', 'type' => 'textarea', 'required' => true, 'rows' => 6],
                        ['name' => 'content', 'label' => 'Nội dung bài viết', 'type' => 'textarea', 'rows' => 12, 'hint' => 'Toàn bộ nội dung bài viết. Xuống dòng 2 lần để tách đoạn.'],
                    ]"
                    :values="$item?->translation('vi')?->toArray() ?? []"
                />

                <x-admin.partials.translation-section
                    locale="en"
                    label="English"
                    :fields="[
                        ['name' => 'title', 'label' => 'Title', 'max' => 250],
                        ['name' => 'excerpt', 'label' => 'Excerpt', 'type' => 'textarea', 'rows' => 6],
                        ['name' => 'content', 'label' => 'Content', 'type' => 'textarea', 'rows' => 12, 'hint' => 'Full article content. Double newline to split paragraphs.'],
                    ]"
                    :values="$item?->translation('en')?->toArray() ?? []"
                />
            </div>

            <div class="flex items-center gap-3 mb-8">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $item ? 'Lưu thay đổi' : 'Tạo bài viết' }}
                </button>
                <a href="{{ route('admin.news.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
