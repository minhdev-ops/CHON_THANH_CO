@forelse ($projects as $project)
    <tr class="hover:bg-gray-50">
        <td class="px-4 py-3">
            <input type="checkbox" name="ids[]" value="{{ $project->id }}" class="row-check rounded border-gray-300">
        </td>
        <td class="px-4 py-3">
            <img src="{{ $project->hero_image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
        </td>
        <td class="px-4 py-3 font-medium max-w-xs">{{ $project->name }}</td>
        <td class="px-4 py-3 text-gray-600 max-w-[10rem] truncate">{{ $project->location }}</td>
        <td class="px-4 py-3 text-gray-600">{{ $project->period }}</td>
        <td class="px-4 py-3 text-gray-600">{{ $project->area ?? '—' }}</td>
        <td class="px-4 py-3 text-center">
            @if ($project->is_featured)
                <span class="text-amber-500 material-symbols-outlined">star</span>
            @else
                <span class="text-gray-300">—</span>
            @endif
        </td>
        <td class="px-4 py-3 text-center">
            @if ($project->is_active)
                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
            @else
                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
            @endif
        </td>
        <td class="px-4 py-3 text-right whitespace-nowrap">
            <a href="{{ route('admin.projects.edit', $project) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
            <form method="POST" action="{{ route('admin.projects.destroy', $project) }}" class="inline" onsubmit="return confirm('Xóa dự án này?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
            </form>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="9" class="px-4 py-8 text-center text-gray-500">Không có dự án nào.</td>
    </tr>
@endforelse
