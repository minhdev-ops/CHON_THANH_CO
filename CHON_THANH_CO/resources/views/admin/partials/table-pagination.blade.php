{{-- Footer bảng AJAX: số dòng mỗi trang + thông tin + phân trang (Alpine adminTable scope) --}}
<div class="mt-4 flex flex-wrap items-center justify-between gap-3">
    <div class="flex items-center gap-4 text-sm text-gray-500">
        <label class="flex items-center gap-2">
            Hiển thị
            <select name="per_page" x-model.number="perPage" @change="changePerPage()"
                class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="15">15</option>
                <option value="25">25</option>
                <option value="50">50</option>
                <option value="100">100</option>
            </select>
            dòng / trang
        </label>
        <span data-table-info x-text="infoText"></span>
    </div>

    <nav class="flex items-center gap-1" x-show="totalPages > 1" x-cloak aria-label="Phân trang">
        <button type="button" @click="goTo(1)" :disabled="page <= 1"
            class="px-3 py-1.5 rounded border text-sm disabled:opacity-40 hover:bg-gray-50 border-gray-300">«</button>
        <button type="button" @click="goTo(page - 1)" :disabled="page <= 1"
            class="px-3 py-1.5 rounded border text-sm disabled:opacity-40 hover:bg-gray-50 border-gray-300">‹ Trước</button>
        <template x-for="p in pageLinks" :key="p">
            <button type="button" :data-page="p" @click="goTo(p)" x-text="p"
                :class="p === page
                    ? 'px-3 py-1.5 rounded border text-sm bg-blue-600 border-blue-600 text-white font-semibold'
                    : 'px-3 py-1.5 rounded border text-sm border-gray-300 hover:bg-gray-50'"></button>
        </template>
        <button type="button" @click="goTo(page + 1)" :disabled="page >= totalPages"
            class="px-3 py-1.5 rounded border text-sm disabled:opacity-40 hover:bg-gray-50 border-gray-300">Sau ›</button>
        <button type="button" @click="goTo(totalPages)" :disabled="page >= totalPages"
            class="px-3 py-1.5 rounded border text-sm disabled:opacity-40 hover:bg-gray-50 border-gray-300">»</button>
    </nav>
</div>
