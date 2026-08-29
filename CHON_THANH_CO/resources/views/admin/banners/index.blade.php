@extends('admin.layouts.app')

@section('title', 'Banner & Hero')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Quản lý banner hero trang chủ và banner CTA.</p>
        <a href="{{ route('admin.banners.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm banner
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Vị trí</th>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tiêu đề</th>
                    <th class="px-4 py-3">Liên kết</th>
                    <th class="px-4 py-3 text-center">Thứ tự</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($banners as $banner)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="inline-block bg-slate-100 text-slate-700 text-xs px-2 py-1 rounded font-medium">
                                {{ $sectionLabels[$banner->section] ?? $banner->section }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            @if ($banner->image)
                                <img src="{{ $banner->image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium max-w-xs">{{ $banner->translation('vi')?->title }}</td>
                        <td class="px-4 py-3 text-gray-500 max-w-xs truncate">{{ $banner->link_to ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $banner->sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($banner->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.banners.edit', $banner) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.banners.destroy', $banner) }}" class="inline" onsubmit="return confirm('Xóa banner này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Chưa có banner nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
