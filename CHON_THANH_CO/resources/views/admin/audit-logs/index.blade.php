@extends('admin.layouts.app')

@section('title', 'Nhật ký hoạt động')

@section('content')
    <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
        <p class="text-sm text-gray-500">Ghi lại mọi thao tác tạo, sửa, xóa nội dung trên hệ thống.</p>

        <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-2 flex-wrap">
            <select name="action"
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả hành động</option>
                @foreach ($actionLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('action') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="model_type"
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả đối tượng</option>
                @foreach ($modelLabels as $value => $label)
                    <option value="{{ $value }}" {{ request('model_type') === $value ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" name="actor" value="{{ request('actor') }}" placeholder="Người thực hiện..."
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40">
            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold px-4 py-2 rounded">Lọc</button>
            @if (request()->hasAny(['action', 'model_type', 'actor']))
                <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
            @endif
        </form>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3">Thời gian</th>
                    <th class="px-4 py-3">Người thực hiện</th>
                    <th class="px-4 py-3">Hành động</th>
                    <th class="px-4 py-3">Đối tượng</th>
                    <th class="px-4 py-3">Chi tiết</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
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
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $logs->links() }}
    </div>
@endsection
