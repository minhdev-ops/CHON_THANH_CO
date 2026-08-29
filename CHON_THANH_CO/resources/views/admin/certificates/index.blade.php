@extends('admin.layouts.app')

@section('title', 'Chứng chỉ')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <p class="text-sm text-gray-500">Chứng chỉ, ủy quyền và hồ sơ năng lực.</p>
        <a href="{{ route('admin.certificates.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm chứng chỉ
        </a>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tên</th>
                    <th class="px-4 py-3">Slug</th>
                    <th class="px-4 py-3 text-center">Thứ tự</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($certificates as $certificate)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <img src="{{ $certificate->image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
                        </td>
                        <td class="px-4 py-3 font-medium max-w-xs">
                            {{ $certificate->translation('vi')?->name }}
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ $certificate->slug }}</td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $certificate->sort_order }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($certificate->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.certificates.edit', $certificate) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.certificates.destroy', $certificate) }}" class="inline" onsubmit="return confirm('Xóa chứng chỉ này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-500">Chưa có chứng chỉ nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
