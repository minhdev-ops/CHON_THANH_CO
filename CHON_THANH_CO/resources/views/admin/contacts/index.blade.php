@extends('admin.layouts.app')

@section('title', 'Liên hệ')

@section('content')
    @php
        $tableConfig = [
            'url' => route('admin.contacts.index'),
            'loginUrl' => route('admin.login'),
            'bodyId' => 'table-body',
            'q' => '',
            'sort' => request('sort'),
            'dir' => request('dir'),
            'page' => $contacts->currentPage(),
            'perPage' => $contacts->perPage(),
            'total' => $contacts->total(),
        ];
    @endphp

    <div x-data='adminTable({!! e(json_encode($tableConfig)) !!})'>
        <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
            <p class="text-sm text-gray-500">Tin nhắn từ khách hàng gửi qua form liên hệ trên website.</p>

            <div class="flex items-center gap-2">
                <select name="status" data-table-filter @change="page = 1; load()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tất cả trạng thái</option>
                    <option value="new" @selected(request('status') === 'new')>Mới</option>
                    <option value="replied" @selected(request('status') === 'replied')>Đã xử lý</option>
                </select>
                @if (request('status'))
                    <a href="{{ route('admin.contacts.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
                @endif
            </div>
        </div>

        <div class="relative">
            <div x-show="loading" x-cloak class="absolute inset-0 bg-white/60 z-20 flex items-center justify-center">
                <span class="inline-block h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></span>
            </div>

            <div x-show="error" x-cloak class="bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5 mb-4 rounded-lg flex items-center justify-between">
                <span x-text="error"></span>
                <button type="button" @click="load()" class="font-semibold underline">Thử lại</button>
            </div>

            <div id="table-body" class="space-y-4">
                @include('admin.contacts.rows')
            </div>
        </div>

        @include('admin.partials.table-pagination')
    </div>
@endsection
