@forelse ($partners as $partner)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3">
            <img src="{{ $partner->image }}" alt="{{ $partner->name }}" class="h-8 w-auto object-contain bg-gray-50 border border-gray-200 rounded px-1">
        </td>
        <td class="px-4 py-3 font-medium">{{ $partner->name }}</td>
        <td class="px-4 py-3 text-center text-gray-500">{{ $partner->sort_order }}</td>
        <td class="px-4 py-3 text-center">
            @if ($partner->is_active)
                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
            @else
                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
            @endif
        </td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.partners.edit', $partner) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
            <form method="POST" action="{{ route('admin.partners.destroy', $partner) }}" class="inline" onsubmit="return confirm('Xóa đối tác này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có đối tác nào.</td>
    </tr>
@endforelse
