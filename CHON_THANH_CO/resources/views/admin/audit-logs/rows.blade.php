@forelse ($logs as $log)
    <tr class="hover:bg-gray-50 align-top" x-data="{ open: false }">
        <td class="px-4 py-3 text-gray-500 whitespace-nowrap">{{ $log->created_at->format('H:i d/m/Y') }}</td>
        <td class="px-4 py-3 font-medium">{{ $log->actor }}</td>
        <td class="px-4 py-3">
            @if ($log->action === 'created')
                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">{{ $actionLabels[$log->action] ?? $log->action }}</span>
            @elseif ($log->action === 'updated')
                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-1 rounded">{{ $actionLabels[$log->action] ?? $log->action }}</span>
            @else
                <span class="bg-red-100 text-red-700 text-xs px-2 py-1 rounded">{{ $actionLabels[$log->action] ?? $log->action }}</span>
            @endif
        </td>
        <td class="px-4 py-3">
            <div class="font-medium">{{ $modelLabels[$log->model_type] ?? class_basename($log->model_type) }}</div>
            <div class="text-xs text-gray-400">#{{ $log->model_id }}</div>
        </td>
        <td class="px-4 py-3">
            <button type="button" @click="open = !open" class="text-blue-600 hover:underline text-sm inline-flex items-center gap-1">
                <span x-text="open ? 'Ẩn chi tiết' : 'Xem chi tiết'"></span>
                <span class="material-symbols-outlined text-[16px]" :class="open ? 'rotate-180' : ''">expand_more</span>
            </button>
            <pre x-show="open" x-cloak
                class="mt-2 bg-gray-50 border border-gray-200 rounded p-3 text-xs text-gray-700 overflow-x-auto whitespace-pre-wrap">{{ json_encode($log->changes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-4 py-8 text-center text-gray-500">Chưa có hoạt động nào.</td>
    </tr>
@endforelse
