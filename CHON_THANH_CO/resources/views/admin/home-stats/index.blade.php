@extends('admin.layouts.app')

@section('title', 'Số liệu năng lực')

@section('content')
    @php
        $tableConfig = [
            'url' => route('admin.home-stats.index'),
            'loginUrl' => route('admin.login'),
            'bodyId' => 'table-body',
            'q' => request('q'),
            'sort' => request('sort'),
            'dir' => request('dir'),
            'page' => $stats->currentPage(),
            'perPage' => $stats->perPage(),
            'total' => $stats->total(),
        ];
    @endphp

    <div x-data='adminTable({!! e(json_encode($tableConfig)) !!})'>
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm theo nhãn, giá trị..."
                    x-model="q" @input.debounce.300ms="search()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                @if (request('q'))
                    <a href="{{ route('admin.home-stats.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
                @endif
            </div>
            <a href="{{ route('admin.home-stats.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span> Thêm số liệu
            </a>
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
                        <th class="px-4 py-3">Icon</th>
                        <th class="px-4 py-3">Giá trị</th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="label" @click="sortBy('label')">
                            Nhãn <span class="text-[10px] text-gray-400" x-text="sortIcon('label')"></span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer select-none hover:text-gray-700" data-sort="sort_order" @click="sortBy('sort_order')">
                            Thứ tự <span class="text-[10px] text-gray-400" x-text="sortIcon('sort_order')"></span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer select-none hover:text-gray-700" data-sort="is_active" @click="sortBy('is_active')">
                            Trạng thái <span class="text-[10px] text-gray-400" x-text="sortIcon('is_active')"></span>
                        </th>
                        <th class="px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="table-body">
                    @include('admin.home-stats.rows')
                </tbody>
            </table>
        </div>

        @include('admin.partials.table-pagination')
    </div>
@endsection
