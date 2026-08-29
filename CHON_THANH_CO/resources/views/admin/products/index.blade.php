@extends('admin.layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <form method="GET" action="{{ route('admin.products.index') }}" class="flex items-center gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tên, mã, slug..."
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
            <select name="category" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả danh mục</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->translation('vi')?->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded">Lọc</button>
            @if (request('q') || request('category'))
                <a href="{{ route('admin.products.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
            @endif
        </form>
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

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm" id="bulk-table">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300" title="Chọn tất cả">
                    </th>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tên / Mã</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3 text-center">Cường độ</th>
                    <th class="px-4 py-3 text-center">Đặc điểm</th>
                    <th class="px-4 py-3 text-center">Nổi bật</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($products as $product)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $product->id }}" class="row-check rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <img src="{{ $product->image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $product->translation('vi')?->name }}</div>
                            <div class="text-xs text-gray-500">{{ $product->code }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600">{{ $product->category?->translation('vi')?->name }}</td>
                        <td class="px-4 py-3 text-center text-gray-600">
                            {{ $product->strength_min !== null ? $product->strength_min : '—' }}
                            @if ($product->strength_max !== null && $product->strength_max != $product->strength_min)
                                – {{ $product->strength_max }}
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-gray-500">{{ $product->specs_count }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($product->is_featured)
                                <span class="text-amber-500 material-symbols-outlined">star</span>
                            @else
                                <span class="text-gray-300">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($product->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.products.edit', $product) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" class="inline" onsubmit="return confirm('Xóa sản phẩm này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="px-4 py-8 text-center text-gray-500">Không có sản phẩm nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $products->links() }}
    </div>
@endsection

@push('scripts')
    <script>
        // Bulk actions: checkbox chọn tất cả + đếm + xác nhận xóa
        (function () {
            const table = document.getElementById('bulk-table');
            if (!table) return;

            const selectAll = document.getElementById('select-all');
            const countLabel = document.getElementById('bulk-count');
            const rows = table.querySelectorAll('.row-check');

            const refresh = () => {
                const n = table.querySelectorAll('.row-check:checked').length;
                countLabel.textContent = n;
                selectAll.checked = rows.length > 0 && n === rows.length;
                selectAll.indeterminate = n > 0 && n < rows.length;
            };

            selectAll.addEventListener('change', () => {
                rows.forEach((cb) => { cb.checked = selectAll.checked; });
                refresh();
            });
            rows.forEach((cb) => cb.addEventListener('change', refresh));
        })();

        function handleBulkSubmit(e, label) {
            const form = e.target;
            const checked = document.querySelectorAll('#bulk-table .row-check:checked');

            if (checked.length === 0) {
                e.preventDefault();
                alert('Vui lòng chọn ít nhất một ' + label + ' trước khi áp dụng.');
                return false;
            }

            const action = form.querySelector('select[name="action"]').value;
            if (action === 'delete' && !confirm(`Xóa ${checked.length} ${label} đã chọn? Hành động này không thể hoàn tác.`)) {
                e.preventDefault();
                return false;
            }

            // Checkbox nằm ngoài form nên cần chép id đã chọn vào form trước khi submit
            form.querySelectorAll('input[name="ids[]"]').forEach((i) => i.remove());
            checked.forEach((cb) => {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = 'ids[]';
                hidden.value = cb.value;
                form.appendChild(hidden);
            });

            return true;
        }
    </script>
@endpush
