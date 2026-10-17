@forelse ($items as $item)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3 text-gray-600"><span class="material-symbols-outlined">{{ $item->icon }}</span></td>
        <td class="px-4 py-3 font-medium max-w-md">
            {{ $item->title }}
            <p class="text-xs text-gray-500 truncate">{{ $item->description }}</p>
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
