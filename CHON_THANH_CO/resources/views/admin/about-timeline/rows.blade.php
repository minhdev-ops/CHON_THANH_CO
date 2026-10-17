@forelse ($items as $item)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3 font-semibold whitespace-nowrap">{{ $item->year }}</td>
        <td class="px-4 py-3 text-gray-600 max-w-md">
            <p class="truncate">{{ $item->description }}</p>
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
            <a href="{{ route('admin.about-timeline.edit', $item) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
            <form method="POST" action="{{ route('admin.about-timeline.destroy', $item) }}" class="inline" onsubmit="return confirm('Xóa mốc lịch sử này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có mốc lịch sử nào.</td>
    </tr>
@endforelse
