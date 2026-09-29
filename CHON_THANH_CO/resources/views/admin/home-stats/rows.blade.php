@forelse ($stats as $stat)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3 text-gray-600"><span class="material-symbols-outlined">{{ $stat->icon }}</span></td>
        <td class="px-4 py-3 font-semibold">{{ $stat->value }}</td>
        <td class="px-4 py-3 text-gray-600">{{ $stat->label }}</td>
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
