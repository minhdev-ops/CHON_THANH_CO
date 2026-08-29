@extends('admin.layouts.app')

@section('title', 'Số liệu năng lực')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Các số liệu nổi bật hiển thị trên trang chủ (năm kinh nghiệm, nhà phân phối...).</p>
        <a href="{{ route('admin.home-stats.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm số liệu
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Icon</th>
                    <th class="px-4 py-3">Giá trị</th>
                    <th class="px-4 py-3">Nhãn</th>
                    <th class="px-4 py-3 text-center">Thứ tự</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($stats as $stat)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-gray-600"><span class="material-symbols-outlined">{{ $stat->icon }}</span></td>
                        <td class="px-4 py-3 font-semibold">{{ $stat->value }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $stat->translation('vi')?->label }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $stat->sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($stat->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.home-stats.edit', $stat) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.home-stats.destroy', $stat) }}" class="inline" onsubmit="return confirm('Xóa số liệu này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có số liệu nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
