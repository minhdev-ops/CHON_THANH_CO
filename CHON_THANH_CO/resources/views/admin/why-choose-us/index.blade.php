@extends('admin.layouts.app')

@section('title', 'Lý do chọn')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Các lý do khách hàng nên chọn CHON THANH (hiển thị trên trang chủ).</p>
        <a href="{{ route('admin.why-choose-us.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm mục
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Icon</th>
                    <th class="px-4 py-3">Tiêu đề</th>
                    <th class="px-4 py-3 text-center">Thứ tự</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($items as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600"><span class="material-symbols-outlined">{{ $item->icon }}</span></td>
                        <td class="px-4 py-3 font-medium max-w-md">
                            {{ $item->translation('vi')?->title }}
                            <p class="text-xs text-gray-500 truncate">{{ $item->translation('vi')?->description }}</p>
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $item->sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.why-choose-us.edit', $item) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.why-choose-us.destroy', $item) }}" class="inline" onsubmit="return confirm('Xóa mục này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có mục nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
