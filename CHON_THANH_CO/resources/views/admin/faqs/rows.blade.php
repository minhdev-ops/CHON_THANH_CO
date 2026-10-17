@forelse ($faqs as $faq)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3 font-medium max-w-lg">{{ $faq->question }}</td>
        <td class="px-4 py-3 text-center text-gray-500">{{ $faq->sort_order }}</td>
        <td class="px-4 py-3 text-center">
            @if ($faq->is_active)
                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
            @else
                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
            @endif
        </td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.faqs.edit', $faq) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
            <form method="POST" action="{{ route('admin.faqs.destroy', $faq) }}" class="inline" onsubmit="return confirm('Xóa FAQ này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="4" class="px-4 py-8 text-center text-gray-500">Chưa có FAQ nào.</td>
    </tr>
@endforelse
