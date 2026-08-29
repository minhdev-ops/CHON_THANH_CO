@extends('admin.layouts.app')

@section('title', 'Danh mục tin tức')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Quản lý danh mục cho các bài viết tin tức.</p>
        <a href="{{ route('admin.news-categories.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm danh mục
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3 text-center">Bài viết</th>
                    <th class="px-4 py-3 text-center">Thứ tự</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($categories as $category)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $category->translation('vi')?->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $category->slug }}</td>
                        <td class="px-4 py-3 text-center">{{ $category->news_count }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($category->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.news-categories.edit', $category) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.news-categories.destroy', $category) }}" class="inline" onsubmit="return confirm('Xóa danh mục này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có danh mục tin tức nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
