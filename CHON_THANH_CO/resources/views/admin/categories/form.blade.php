@extends('admin.layouts.app')

@section('title', 'Danh mục sản phẩm')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $category ? route('admin.categories.update', $category) : route('admin.categories.store') }}">
            @csrf
            @if ($category)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <x-admin.partials.input name="slug" label="Slug" :value="$category?->slug" :hint="'Để trống để tự tạo từ tên.'" />

                <x-admin.partials.input name="name" label="Tên danh mục" :value="$category?->name" required max="150" />

                <x-admin.partials.input name="description" label="Mô tả" :value="$category?->description" type="textarea" />

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$category?->sort_order" min="0" />
                    <div class="pt-7">
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$category?->is_active ?? true" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $category ? 'Lưu thay đổi' : 'Tạo danh mục' }}
                </button>
                <a href="{{ route('admin.categories.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection