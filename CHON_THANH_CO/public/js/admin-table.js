/**
 * adminTable — component Alpine cho bảng AJAX của admin.
 * Contract JSON: { recordsTotal, recordsFiltered, page, per_page, html }
 * Trigger: ?ajax=1 hoặc header X-Requested-With.
 */
document.addEventListener('alpine:init', () => {
    Alpine.data('adminTable', (config = {}) => ({
        url: config.url || location.pathname,
        loginUrl: config.loginUrl || '/admin/login',
        bodyId: config.bodyId || 'table-body',
        q: config.q || '',
        page: config.page || 1,
        perPage: config.perPage || 15,
        defaultPerPage: config.defaultPerPage || 15,
        total: config.total || 0,
        recordsTotal: config.recordsTotal || config.total || 0,
        sort: config.sort || null,
        dir: config.dir || 'asc',
        loading: false,
        error: null,

        init() {
            const params = new URLSearchParams(location.search)
            const sortInUrl = params.get('sort') && params.get('dir')
            const perPageInUrl = params.get('per_page') && params.get('per_page') !== String(this.defaultPerPage)
            if (sortInUrl || perPageInUrl) {
                this.$nextTick(() => this.load())
            }
        },

        get totalPages() {
            return Math.max(1, Math.ceil(this.total / this.perPage))
        },

        get infoText() {
            if (this.total === 0) return 'Hiển thị 0 / 0 mục'
            const from = (this.page - 1) * this.perPage + 1
            const to = Math.min(this.page * this.perPage, this.total)
            return `Hiển thị ${from}–${to} / ${this.total} mục`
        },

        get pageLinks() {
            const total = this.totalPages
            const window = 2
            let start = Math.max(1, this.page - window)
            let end = Math.min(total, start + window * 2)
            start = Math.max(1, end - window * 2)
            const links = []
            for (let p = start; p <= end; p++) links.push(p)
            return links
        },

        async load() {
            this.loading = true
            this.error = null
            try {
                const url = new URL(this.url, location.origin)
                url.search = ''
                url.searchParams.set('ajax', '1')
                url.searchParams.set('page', String(this.page))
                url.searchParams.set('per_page', String(this.perPage))
                if (this.q) url.searchParams.set('q', this.q)
                if (this.sort) {
                    url.searchParams.set('sort', this.sort)
                    url.searchParams.set('dir', this.dir)
                }
                document.querySelectorAll('[data-table-filter]').forEach((el) => {
                    if (el.value) url.searchParams.set(el.name, el.value)
                })

                const res = await fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        Accept: 'application/json',
                    },
                })

                if (res.status === 401) {
                    location.href = this.loginUrl
                    return
                }
                if (!res.ok) throw new Error('HTTP ' + res.status)

                const data = await res.json()
                const body = document.getElementById(this.bodyId)
                if (body) body.innerHTML = data.html
                this.total = data.recordsFiltered
                this.recordsTotal = data.recordsTotal
                this.page = data.page
                this.perPage = data.per_page
                this.syncUrl()
            } catch (e) {
                this.error = 'Không tải được dữ liệu bảng. Vui lòng thử lại.'
            } finally {
                this.loading = false
            }
        },

        search() {
            this.page = 1
            this.load()
        },

        changePerPage() {
            this.page = 1
            this.load()
        },

        sortBy(column) {
            if (this.sort === column) {
                this.dir = this.dir === 'asc' ? 'desc' : 'asc'
            } else {
                this.sort = column
                this.dir = 'asc'
            }
            this.page = 1
            this.load()
        },

        sortIcon(column) {
            if (this.sort !== column) return '↕'
            return this.dir === 'asc' ? '▲' : '▼'
        },

        goTo(target) {
            const p = Math.min(Math.max(1, target), this.totalPages)
            if (p === this.page || this.loading) return
            this.page = p
            this.load()
        },

        syncUrl() {
            const params = new URLSearchParams(location.search)
            ;['q', 'page', 'per_page', 'sort', 'dir'].forEach((k) => params.delete(k))
            if (this.q) params.set('q', this.q)
            if (this.page > 1) params.set('page', String(this.page))
            if (this.perPage !== this.defaultPerPage) params.set('per_page', String(this.perPage))
            if (this.sort) {
                params.set('sort', this.sort)
                params.set('dir', this.dir)
            }
            const qs = params.toString()
            history.replaceState(null, '', location.pathname + (qs ? '?' + qs : ''))
        },
    }))
})

/**
 * Bulk actions dùng event delegation để hoạt động cả với
 * rows nạp bằng AJAX (thay DOM, không cần re-bind).
 */
function refreshBulkCount() {
    const table = document.getElementById('bulk-table')
    if (!table) return
    const checks = table.querySelectorAll('.row-check')
    const checked = table.querySelectorAll('.row-check:checked')
    const countLabel = document.getElementById('bulk-count')
    const selectAll = document.getElementById('select-all')
    if (countLabel) countLabel.textContent = checked.length
    if (selectAll) {
        selectAll.checked = checks.length > 0 && checked.length === checks.length
        selectAll.indeterminate = checked.length > 0 && checked.length < checks.length
    }
}

document.addEventListener('change', (e) => {
    const target = e.target
    if (!(target instanceof Element)) return
    const table = document.getElementById('bulk-table')
    if (!table || !table.contains(target)) return

    if (target.id === 'select-all') {
        table.querySelectorAll('.row-check').forEach((cb) => {
            cb.checked = target.checked
        })
    }
    refreshBulkCount()
})

function handleBulkSubmit(e, label) {
    const form = e.target
    const checked = document.querySelectorAll('#bulk-table .row-check:checked')

    if (checked.length === 0) {
        e.preventDefault()
        alert('Vui lòng chọn ít nhất một ' + label + ' trước khi áp dụng.')
        return false
    }

    const action = form.querySelector('select[name="action"]').value
    if (action === 'delete' && !confirm(`Xóa ${checked.length} ${label} đã chọn? Hành động này không thể hoàn tác.`)) {
        e.preventDefault()
        return false
    }

    form.querySelectorAll('input[name="ids[]"]').forEach((i) => i.remove())
    checked.forEach((cb) => {
        const hidden = document.createElement('input')
        hidden.type = 'hidden'
        hidden.name = 'ids[]'
        hidden.value = cb.value
        form.appendChild(hidden)
    })

    return true
}
