@extends('admin.layouts.app')

@section('title', 'Ứng dụng')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Quản lý các ứng dụng / lĩnh vực sử dụng sản phẩm.</p>
        <a href="{{ route('admin.applications.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm ứng dụng
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3 text-center">Sản phẩm</th>
                    <th class="px-4 py-3 text-center">Thứ tự</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($applications as $application)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium">{{ $application->translation('vi')?->name }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $application->slug }}</td>
                        <td class="px-4 py-3 text-center">{{ $application->products_count }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $application->sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($application->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.applications.edit', $application) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.applications.destroy', $application) }}" class="inline" onsubmit="return confirm('Xóa ứng dụng này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có ứng dụng nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
