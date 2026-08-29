@extends('admin.layouts.app')

@section('title', 'Tin tức')

@section('content')
    <div class="flex items-center justify-between mb-6">
        <form method="GET" action="{{ route('admin.news.index') }}" class="flex items-center gap-3">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Tìm theo tiêu đề..."
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 w-64">
            <button type="submit" class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-semibold px-4 py-2 rounded">Lọc</button>
            @if (request('q'))
                <a href="{{ route('admin.news.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
            @endif
        </form>
        <a href="{{ route('admin.news.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-2">
            <span class="material-symbols-outlined text-[18px]">add</span> Thêm bài viết
        </a>
    </div>

    {{-- Thanh công cụ hàng loạt --}}
    <form method="POST" action="{{ route('admin.news.bulk') }}" class="bg-white border border-gray-200 rounded-lg p-3 mb-4 flex flex-wrap items-center gap-3"
        onsubmit="return handleBulkSubmit(event, 'bài viết')">
        @csrf
        <span class="text-sm text-gray-500 font-medium">Đã chọn: <strong id="bulk-count" class="text-blue-600">0</strong> mục</span>
        <div class="flex items-center gap-2 ml-auto">
            <select name="action" class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="activate">Hiển thị</option>
                <option value="deactivate">Ẩn</option>
                <option value="delete">Xóa</option>
            </select>
            <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold px-4 py-2 rounded">Áp dụng cho mục đã chọn</button>
        </div>
    </form>

    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <table class="w-full text-sm" id="bulk-table">
            <thead class="bg-gray-50 border-b border-gray-200 text-left text-xs uppercase text-gray-500">
                <tr>
                    <th class="px-4 py-3 w-10">
                        <input type="checkbox" id="select-all" class="rounded border-gray-300" title="Chọn tất cả">
                    </th>
                    <th class="px-4 py-3">Ảnh</th>
                    <th class="px-4 py-3">Tiêu đề</th>
                    <th class="px-4 py-3">Danh mục</th>
                    <th class="px-4 py-3">Ngày đăng</th>
                    <th class="px-4 py-3 text-center">Trạng thái</th>
                    <th class="px-4 py-3 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse ($news as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <input type="checkbox" name="ids[]" value="{{ $item->id }}" class="row-check rounded border-gray-300">
                        </td>
                        <td class="px-4 py-3">
                            <img src="{{ $item->image }}" alt="" class="h-12 w-16 object-cover rounded border border-gray-200">
                        </td>
                        <td class="px-4 py-3 font-medium max-w-md">{{ $item->translation('vi')?->title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->category?->translation('vi')?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $item->published_at?->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($item->is_active)
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-1 rounded">Hiển thị</span>
                            @else
                                <span class="bg-gray-100 text-gray-500 text-xs px-2 py-1 rounded">Ẩn</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('admin.news.edit', $item) }}" class="text-blue-600 hover:underline mr-3">Sửa</a>
                            <form method="POST" action="{{ route('admin.news.destroy', $item) }}" class="inline" onsubmit="return confirm('Xóa bài viết này?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:underline">Xóa</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">Không có bài viết nào.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $news->links() }}
    </div>
@endsection

@push('scripts')
    <script>
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
