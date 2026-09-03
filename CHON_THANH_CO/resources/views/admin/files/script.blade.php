<script>
    function fileBrowser(options) {
        return {
            mode: options.mode,
            inputId: options.inputId || '',
            type: options.initialType || 'Images',
            initialFolder: options.initialFolder || '',
            folder: options.initialFolder || '',
            shortcuts: options.shortcuts || [],
            folders: [],
            files: [],
            loading: true,
            error: '',
            uploading: false,
            uploadDone: 0,
            uploadTotal: 0,
            uploadResults: [],
            dragDepth: 0,
            dragging: false,
            notice: '',
            noticeType: 'ok',
            _noticeTimer: null,
            openCreate: false,
            newFolderName: '',

            init() {
                this.load();

                // Chặn trình duyệt mở file khi thả ra ngoài vùng kéo thả
                ['dragover', 'drop'].forEach((evt) => {
                    document.addEventListener(evt, (e) => e.preventDefault());
                });

                // Fallback: nếu kéo ra khỏi cửa sổ, dragleave có thể không kích hoạt
                window.addEventListener('dragend', () => {
                    this.dragDepth = 0;
                    this.dragging = false;
                });
            },

            apiPath(action) {
                return `{{ url(config('admin.path', 'admin') . '/files') }}/${action}?type=${encodeURIComponent(this.type)}&folder=${encodeURIComponent(this.folder)}`;
            },

            csrf() {
                const meta = document.querySelector('meta[name="csrf-token"]');
                return meta ? meta.content : '';
            },

            apiHeaders() {
                return { 'X-CSRF-TOKEN': this.csrf(), 'Accept': 'application/json' };
            },

            sessionExpiredMessage() {
                return 'Phiên đăng nhập đã hết hạn. Vui lòng tải lại trang để đăng nhập lại.';
            },

            async parseResponse(res) {
                let data = {};
                try {
                    data = await res.json();
                } catch (e) {
                    // Response không phải JSON: thường là trang HTML (419 CSRF / redirect login)
                    if (res.status === 419 || res.status === 401) {
                        return { error: this.sessionExpiredMessage() };
                    }
                    return { error: 'Máy chủ trả về phản hồi không hợp lệ. Vui lòng thử lại.' };
                }
                if (res.status === 419 || res.status === 401) {
                    return { error: this.sessionExpiredMessage() };
                }
                return data;
            },

            errorText(data) {
                if (data && data.error) return data.error;
                if (data && data.message) return data.message;
                if (data && data.errors) return Object.values(data.errors).flat().join(', ');
                return 'Đã có lỗi xảy ra.';
            },

            showNotice(msg, type) {
                this.notice = msg;
                this.noticeType = type || 'ok';
                if (this._noticeTimer) clearTimeout(this._noticeTimer);
                this._noticeTimer = setTimeout(() => { this.notice = ''; }, 7000);
            },

            async load() {
                this.loading = true;
                this.error = '';
                try {
                    const res = await fetch(this.apiPath('browse'), { headers: { Accept: 'application/json' } });
                    const data = await this.parseResponse(res);
                    if (!res.ok) { this.error = data.error || 'Không tải được danh sách file.'; return; }
                    this.folders = data.folders || [];
                    this.files = data.files || [];
                } catch (e) {
                    this.error = 'Đã có lỗi xảy ra khi tải danh sách.';
                } finally {
                    this.loading = false;
                }
            },

            switchType(type) {
                if (this.type === type) return;
                this.type = type;
                this.folder = '';
                this.load();
            },

            enterFolder(name) {
                this.folder = this.folder ? this.folder + '/' + name : name;
                this.load();
            },

            upFolder() {
                const idx = this.folder.lastIndexOf('/');
                this.folder = idx === -1 ? '' : this.folder.slice(0, idx);
                this.load();
            },

            goFolder(path) {
                this.folder = path || '';
                this.load();
            },

            crumbParts() {
                return this.folder ? this.folder.split('/') : [];
            },

            crumbPath(index) {
                return this.crumbParts().slice(0, index + 1).join('/');
            },

            async createFolder() {
                const name = this.newFolderName.trim();
                if (!name) return;
                const fd = new FormData();
                fd.append('type', this.type);
                fd.append('folder', this.folder);
                fd.append('name', name);
                const res = await fetch(@json(route('admin.files.create-folder')), {
                    method: 'POST',
                    headers: this.apiHeaders(),
                    body: fd,
                });
                const data = await this.parseResponse(res);
                if (!res.ok) { alert(this.errorText(data)); return; }
                this.newFolderName = '';
                this.openCreate = false;
                this.showNotice(`Đã tạo thư mục "${name}".`, 'ok');
                this.load();
            },

            // ---- Upload (nhiều file) ----

            uploadFiles(event) {
                const input = event.target;
                const files = Array.from(input.files || []);
                input.value = '';
                if (files.length) this.uploadMany(files);
            },

            uploadMany(files) {
                if (this.uploading) return;
                this.uploading = true;
                this.uploadDone = 0;
                this.uploadTotal = files.length;
                this.uploadResults = [];
                this.showNotice(`Đang tải lên ${files.length} file...`, 'info');

                (async () => {
                    for (const file of files) {
                        await this.uploadOne(file);
                        this.uploadDone++;
                    }
                })().then(() => {
                    const ok = this.uploadResults.filter((r) => r.ok).length;
                    const failed = this.uploadResults.filter((r) => !r.ok);

                    if (failed.length === 0) {
                        this.showNotice(`Đã tải lên thành công ${ok} file.`, 'ok');
                    } else {
                        const details = failed.map((r) => `${r.name}: ${r.error}`).join('; ');
                        this.showNotice(`Tải lên ${ok}/${this.uploadResults.length} file. ${failed.length} file lỗi — ${details}`, 'err');
                    }

                    this.load();
                }).finally(() => {
                    this.uploading = false;
                });
            },

            async uploadOne(file) {
                const fd = new FormData();
                fd.append('type', this.type);
                fd.append('folder', this.folder);
                fd.append('file', file);
                try {
                    const res = await fetch(@json(route('admin.files.upload')), {
                        method: 'POST',
                        headers: this.apiHeaders(),
                        body: fd,
                    });
                    const data = await this.parseResponse(res);
                    if (!res.ok) {
                        this.uploadResults.push({ name: file.name, ok: false, error: this.errorText(data) });
                        return;
                    }
                    this.uploadResults.push({ name: file.name, ok: true });
                } catch (e) {
                    this.uploadResults.push({ name: file.name, ok: false, error: 'Lỗi kết nối' });
                }
            },

            // ---- Kéo & thả ----

            hasFiles(e) {
                return e.dataTransfer && Array.from(e.dataTransfer.types || []).includes('Files');
            },

            onDragEnter(e) {
                if (!this.hasFiles(e)) return;
                e.preventDefault();
                this.dragDepth++;
                this.dragging = true;
            },

            onDragOver(e) {
                if (!this.hasFiles(e)) return;
                e.preventDefault();
            },

            onDragLeave(e) {
                if (!this.hasFiles(e)) return;
                e.preventDefault();
                this.dragDepth = Math.max(0, this.dragDepth - 1);
                if (this.dragDepth === 0) this.dragging = false;
            },

            onDrop(e) {
                e.preventDefault();
                this.dragDepth = 0;
                this.dragging = false;
                const files = Array.from(e.dataTransfer?.files || []);
                if (files.length) this.uploadMany(files);
            },

            async remove(name, isFolder) {
                const label = isFolder ? `Xóa thư mục "${name}" và toàn bộ nội dung bên trong?` : `Xóa file "${name}"?`;
                if (!confirm(label)) return;
                const fd = new FormData();
                fd.append('type', this.type);
                fd.append('folder', this.folder);
                fd.append('name', name);
                fd.append('_method', 'DELETE');
                const res = await fetch(@json(route('admin.files.destroy')), {
                    method: 'POST',
                    headers: this.apiHeaders(),
                    body: fd,
                });
                const data = await this.parseResponse(res);
                if (!res.ok) { alert(this.errorText(data)); return; }
                this.showNotice(`Đã xóa "${name}".`, 'ok');
                this.load();
            },

            async rename(name, isFolder) {
                const newName = prompt((isFolder ? 'Đổi tên thư mục' : 'Đổi tên file') + ` "${name}" thành:`, name);
                if (!newName || newName === name) return;
                const fd = new FormData();
                fd.append('type', this.type);
                fd.append('folder', this.folder);
                fd.append('name', name);
                fd.append('new_name', newName);
                const res = await fetch(@json(route('admin.files.rename')), {
                    method: 'POST',
                    headers: this.apiHeaders(),
                    body: fd,
                });
                const data = await this.parseResponse(res);
                if (!res.ok) { alert(this.errorText(data)); return; }
                this.showNotice(`Đã đổi tên thành "${newName}".`, 'ok');
                this.load();
            },

            choose(file) {
                if (this.mode !== 'picker') return;
                const opener = window.opener;
                if (!opener) { alert('Không tìm thấy cửa sổ gốc.'); return; }
                const input = opener.document.getElementById(this.inputId);
                if (!input) { alert('Không tìm thấy ô nhập liệu gốc.'); return; }
                input.value = file.url;
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                if (typeof opener.updateImagePreview === 'function') {
                    opener.updateImagePreview(opener.document.getElementById(this.inputId + '_preview'), file.url);
                }
                window.close();
            },
        };
    }
</script>
