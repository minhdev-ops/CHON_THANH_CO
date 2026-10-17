@forelse ($applications as $application)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3 font-medium">{{ $application->name }}</td>
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
