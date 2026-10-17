@extends('admin.layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    @php
        $tableConfig = [
            'url' => route('admin.products.index'),
            'loginUrl' => route('admin.login'),
            'bodyId' => 'table-body',
            'q' => request('q'),
            'sort' => request('sort'),
            'dir' => request('dir'),
            'page' => $products->currentPage(),
            'perPage' => $products->perPage(),
            'total' => $products->total(),
        ];
    @endphp

    <div x-data='adminTable({!! e(json_encode($tableConfig)) !!})'>
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <input type="search" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, mã, slug..."
                    x-model="q" @input.debounce.300ms="search()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
                <select name="category" data-table-filter @change="page = 1; load()"
                    class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Tất cả danh mục</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                @if (request('q') || request('category'))
                    <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
                @endif
            </div>
            <div class="flex items-center gap-2 flex-wrap">
                <a href="{{ route('admin.files.index', ['type' => 'Images', 'folder' => 'products']) }}" target="_blank" title="Mở thư mục ảnh sản phẩm trong Quản lý file"
                    class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">folder_open</span> Mở thư mục chứa
                </a>
                <a href="{{ route('admin.products.template') }}" title="Tải file mẫu để điền dữ liệu"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">download</span> File mẫu
                </a>
                <a href="{{ route('admin.products.export') }}" title="Xuất toàn bộ sản phẩm ra Excel"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded flex items-center gap-2 transition-colors">
                    <span class="material-symbols-outlined text-[18px]">file_download</span> Export Excel
                </a>
                <form method="POST" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="flex items-center gap-2">
                    @csrf
                    <label for="import-file" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2 cursor-pointer transition-colors">
                        <span class="material-symbols-outlined text-[18px]">file_upload</span> Import Excel
                        <input id="import-file" type="file" name="file" accept=".xlsx,.xls,.csv" class="hidden" onchange="this.form.submit()">
                    </label>
                </form>
                <a href="{{ route('admin.products.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add</span> Thêm sản phẩm
                </a>
            </div>
        </div>

        {{-- Thanh công cụ hàng loạt --}}
        <form method="POST" action="{{ route('admin.products.bulk') }}" class="bg-white border border-gray-200 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-3"
            onsubmit="return handleBulkSubmit(event, 'sản phẩm')">
            @csrf
            <span class="text-sm text-gray-500 font-medium">Đã chọn: <strong id="bulk-count" class="text-blue-600">0</strong> mục</span>
            <div class="flex items-center gap-2 ml-auto">
                <select name="action" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="feature">Đánh dấu nổi bật</option>
                    <option value="unfeature">Bỏ nổi bật</option>
                    <option value="activate">Hiển thị</option>
                    <option value="deactivate">Ẩn</option>
                    <option value="delete">Xóa</option>
                </select>
                <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold px-4 py-2 rounded">Áp dụng cho mục đã chọn</button>
            </div>
        </form>

        <div class="bg-blue-50 border border-blue-200 text-blue-800 text-xs rounded-lg px-4 py-2.5 mb-4 leading-relaxed">
            <strong>Import Excel:</strong> hàng được nhận diện theo cột <code>code</code>. Nếu <code>code</code> đã tồn tại → <strong>cập nhật</strong> những cột có dữ liệu (ô để trống giữ nguyên); nếu chưa có → <strong>tạo mới</strong>. Cột <code>category</code> và <code>applications</code> nhập theo slug (hoặc tên), <code>is_featured</code>/<code>is_active</code> nhập 1/0, có/không.
        </div>

        <div class="bg-white border border-gray-200 rounded-lg overflow-hidden relative">
            <div x-show="loading" x-cloak class="absolute inset-0 bg-white/60 z-20 flex items-center justify-center">
                <span class="inline-block h-8 w-8 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></span>
            </div>

            <div x-show="error" x-cloak class="bg-red-50 border-b border-red-200 text-red-700 text-sm px-4 py-2.5 flex items-center justify-between">
                <span x-text="error"></span>
                <button type="button" @click="load()" class="font-semibold underline">Thử lại</button>
            </div>

            <table class="w-full text-sm" id="bulk-table">
                <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                    <tr>
                        <th class="px-4 py-3 w-10">
                            <input type="checkbox" id="select-all" class="rounded border-gray-300" title="Chọn tất cả">
                        </th>
                        <th class="px-4 py-3">Ảnh</th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="name" @click="sortBy('name')">
                            Tên / Mã <span class="text-[10px] text-gray-400" x-text="sortIcon('name')"></span>
                        </th>
                        <th class="px-4 py-3 cursor-pointer select-none hover:text-gray-700" data-sort="category_id" @click="sortBy('category_id')">
                            Danh mục <span class="text-[10px] text-gray-400" x-text="sortIcon('category_id')"></span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer select-none hover:text-gray-700" data-sort="strength_min" @click="sortBy('strength_min')">
                            Cường độ <span class="text-[10px] text-gray-400" x-text="sortIcon('strength_min')"></span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer select-none hover:text-gray-700" data-sort="specs_count" @click="sortBy('specs_count')">
                            Đặc điểm <span class="text-[10px] text-gray-400" x-text="sortIcon('specs_count')"></span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer select-none hover:text-gray-700" data-sort="is_featured" @click="sortBy('is_featured')">
                            Nổi bật <span class="text-[10px] text-gray-400" x-text="sortIcon('is_featured')"></span>
                        </th>
                        <th class="px-4 py-3 text-center cursor-pointer select-none hover:text-gray-700" data-sort="is_active" @click="sortBy('is_active')">
                            Trạng thái <span class="text-[10px] text-gray-400" x-text="sortIcon('is_active')"></span>
                        </th>
                        <th class="px-4 py-3 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100" id="table-body">
                    @include('admin.products.rows')
                </tbody>
            </table>
        </div>

        @include('admin.partials.table-pagination')
    </div>
@endsection
