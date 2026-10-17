@extends('admin.layouts.app')

@section('title', 'Nhật ký hoạt động')

@section('content')
    @php
        $tableConfig = [
            'url' => route('admin.audit-logs.index'),
            'loginUrl' => route('admin.login'),
            'bodyId' => 'table-body',
            'q' => request('actor'),
            'sort' => request('sort'),
            'dir' => request('dir'),
            'page' => $logs->currentPage(),
            'perPage' => $logs->perPage(),
            'total' => $logs->total(),
            'defaultPerPage' => 25,
        ];
    @endphp

    <div x-data='adminTable({!! e(json_encode($tableConfig)) !!})'>
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <p class="text-sm text-gray-500">Ghi lại mọi thao tác tạo, sửa, xóa nội dung trên hệ thống.</p>

            <div class="flex items-center gap-2 flex-wrap">
                <select name="action" data-table-filter @change="page = 1; load()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tất cả hành động</option>
                    @foreach ($actionLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="model_type" data-table-filter @change="page = 1; load()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tất cả đối tượng</option>
                    @foreach ($modelLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('model_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <input type="search" name="actor" value="{{ request('actor') }}" placeholder="Người thực hiện..."
                    x-model="q" @input.debounce.300ms="search()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-40">
                @if (request()->hasAny(['action', 'model_type', 'actor']))
                    <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
                @endif
            </div>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden relative">
            <div x-show="loading" x-cloak class="absolute inset-0 bg-white/60 z-20 flex items-center justify-center">
                <span class="inline-block h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></span>
            </div>

            <div x-show="error" x-cloak class="bg-red-50 border-b border-red-200 text-red-700 text-sm px-4 py-2.5 flex items-center justify-between">
                <span x-text="error"></span>
                <button type="button" @click="load()" class="font-semibold underline">Thử lại</button>
            </div>

            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="created_at" @click="sortBy('created_at')">
                            Thời gian <span class="text-[10px] text-gray-400" x-text="sortIcon('created_at')"></span>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="actor" @click="sortBy('actor')">
                            Người thực hiện <span class="text-[10px] text-gray-400" x-text="sortIcon('actor')"></span>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="action" @click="sortBy('action')">
                            Hành động <span class="text-[10px] text-gray-400" x-text="sortIcon('action')"></span>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="model_type" @click="sortBy('model_type')">
                            Đối tượng <span class="text-[10px] text-gray-400" x-text="sortIcon('model_type')"></span>
                        </th>
                        <th class="px-4 py-3">Chi tiết</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="table-body">
                    @include('admin.audit-logs.rows')
                </tbody>
            </table>
        </div>

        @include('admin.partials.table-pagination')
    </div>
@endsection
