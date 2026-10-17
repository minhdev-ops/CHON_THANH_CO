@forelse ($news as $item)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3">
            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-check rounded border-gray-300">
        </td>
        <td class="px-4 py-3">
            <img src="{{ $item->image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
        </td>
        <td class="px-4 py-3 font-medium max-w-md">{{ $item->title }}</td>
        <td class="px-4 py-3 text-gray-600">{{ $item->category?->name ?? '—' }}</td>
        <td class="px-4 py-3 text-gray-600">{{ $item->published_at?->format('d/m/Y') }}</td>
        <td class="px-4 py-3 text-center">
            @if ($item->is_active)
                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
            @else
                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
            @endif
        </td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.news.edit', $item) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
            <form method="POST" action="{{ route('admin.news.destroy', $item) }}" class="inline" onsubmit="return confirm('Xóa bài viết này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Không có bài viết nào.</td>
    </tr>
@endforelse
