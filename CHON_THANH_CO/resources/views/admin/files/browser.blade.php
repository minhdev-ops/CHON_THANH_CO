<div
    x-data="fileBrowser({
        mode: @js($mode),
        inputId: @js($inputId ?? ''),
        initialType: @js($initialType ?? 'Images'),
        initialFolder: @js($initialFolder ?? ''),
        shortcuts: @js($shortcuts ?? [])
    })"
    class="space-y-5"
    @dragover.prevent="onDragOver($event)"
    @dragenter.prevent="onDragEnter($event)"
    @dragleave.prevent="onDragLeave($event)"
    @drop.prevent="onDrop($event)"
>
    {{-- Overlay khi kéo & thả --}}
    <div x-show="dragging" x-cloak class="fixed inset-0 z-40 pointer-events-none flex items-center justify-center bg-blue-600/10 backdrop-blur-[1px]">
        <div class="bg-white border-4 border-dashed border-blue-400 rounded-2xl px-12 py-10 text-center shadow-2xl">
            <span class="material-symbols-outlined text-5xl text-blue-500">cloud_upload</span>
            <p class="font-semibold text-lg mt-2">Thả file để tải lên</p>
            <p class="text-sm text-gray-500 mt-1">
                Tải lên <strong x-text="type === 'Files' ? 'Tài liệu' : 'Ảnh'"></strong> vào
                <code class="text-gray-700">/userfiles/<span x-text="type === 'Files' ? 'files' : 'images'"></span><span x-text="folder ? '/' + folder : ''"></span>/</code>
            </p>
        </div>
    </div>
    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-lg p-1">
            <button type="button" @click="switchType('Images')" :class="type === 'Images' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                class="px-3 py-1.5 rounded text-sm font-semibold flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">image</span> Ảnh
            </button>
            <button type="button" @click="switchType('Files')" :class="type === 'Files' ? 'bg-blue-600 text-white' : 'text-gray-600 hover:bg-gray-100'"
                class="px-3 py-1.5 rounded text-sm font-semibold flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">folder_zip</span> Tài liệu
            </button>
        </div>

        <div class="flex items-center gap-2">
            <input type="file" x-ref="fileInput" class="hidden" multiple @change="uploadFiles($event)" :accept="type === 'Images' ? 'image/*' : ''">
            <button type="button" @click="$refs.fileInput.click()" :disabled="uploading"
                class="bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]" x-show="!uploading">upload</span>
                <span class="material-symbols-outlined text-[18px] animate-spin" x-show="uploading" x-cloak>progress_activity</span>
                <span x-text="uploading ? ('Đang tải ' + uploadDone + '/' + uploadTotal + '...') : 'Tải lên'"></span>
            </button>
            <button type="button" @click="openCreate = true"
                class="bg-gray-800 hover:bg-gray-700 text-white text-sm font-semibold px-4 py-2 rounded flex items-center gap-1.5 transition-colors">
                <span class="material-symbols-outlined text-[18px]">create_new_folder</span> Tạo thư mục
            </button>
        </div>
    </div>

    {{-- Create folder modal --}}
    <div x-show="openCreate" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @keydown.escape.window="openCreate = false">
        <div class="bg-white rounded-lg shadow-xl p-6 w-full max-w-sm mx-4" @click.outside="openCreate = false">
            <h3 class="font-semibold mb-4 flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">create_new_folder</span> Tạo thư mục mới
            </h3>
            <p class="text-xs text-gray-500 mb-3">
                Tạo trong: <code class="text-gray-700">/userfiles/<span x-text="type === 'Files' ? 'files' : 'images'"></span><span x-text="folder ? '/' + folder : ''"></span>/</code>
            </p>
            <input type="text" x-model="newFolderName" @keydown.enter="createFolder()" placeholder="Tên thư mục (VD: giai-doan-1)"
                class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 mb-4">
            <div class="flex justify-end gap-2">
                <button type="button" @click="openCreate = false" class="text-gray-600 hover:underline text-sm px-3 py-2">Hủy</button>
                <button type="button" @click="createFolder()" class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2 rounded">Tạo</button>
            </div>
        </div>
    </div>

    {{-- Shortcuts (thư mục theo mục) --}}
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs uppercase tracking-wider text-gray-400 font-semibold mr-1">Thư mục nhanh:</span>
        <button type="button" @click="goFolder('')"
            :class="folder === '' ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
            class="border border-gray-200 rounded-full px-3 py-1 text-xs font-semibold transition-colors">
            Gốc
        </button>
        <template x-for="s in shortcuts" :key="s">
            <button type="button" @click="goFolder(s)"
                :class="folder === s ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 hover:bg-gray-50'"
                class="border border-gray-200 rounded-full px-3 py-1 text-xs font-semibold transition-colors" x-text="s">
            </button>
        </template>
    </div>

    {{-- Breadcrumb --}}
    <nav class="flex items-center gap-1 text-sm text-gray-500 flex-wrap">
        <button type="button" @click="goFolder('')" class="hover:text-blue-600 flex items-center gap-1">
            <span class="material-symbols-outlined text-[16px]">folder</span> userfiles
        </button>
        <template x-for="(part, i) in crumbParts()" :key="i">
            <span class="flex items-center gap-1">
                <span class="text-gray-300">/</span>
                <button type="button" @click="goFolder(crumbPath(i))" class="hover:text-blue-600 font-medium" x-text="part"></button>
            </span>
        </template>
        <span x-show="loading" class="text-gray-300 animate-pulse ml-1">đang tải…</span>
    </nav>

    {{-- Content --}}
    <div class="bg-white border border-gray-200 rounded-lg p-4 min-h-[340px]">
        <div x-show="notice" x-cloak class="mb-4 rounded p-3 text-sm flex items-start gap-2"
            :class="noticeType === 'err' ? 'bg-red-50 border border-red-300 text-red-800' : (noticeType === 'info' ? 'bg-blue-50 border border-blue-300 text-blue-800' : 'bg-green-50 border border-green-300 text-green-800')">
            <span class="material-symbols-outlined text-[18px]" x-text="noticeType === 'err' ? 'error' : (noticeType === 'info' ? 'info' : 'check_circle')"></span>
            <span x-text="notice" class="break-all"></span>
        </div>

        <div x-show="error" x-cloak class="mb-4 bg-red-50 border border-red-300 text-red-800 rounded p-3 text-sm">
            <span x-text="error"></span>
        </div>

        <div x-show="loading" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
            <template x-for="i in 12" :key="i">
                <div class="h-28 bg-gray-100 animate-pulse rounded-lg"></div>
            </template>
        </div>

        <div x-show="!loading" x-cloak>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
                {{-- Up button --}}
                <button type="button" x-show="folder" @click="upFolder()"
                    class="border border-dashed border-gray-300 rounded-lg p-3 flex flex-col items-center justify-center gap-1.5 hover:border-blue-400 hover:bg-blue-50 transition-colors text-gray-500">
                    <span class="material-symbols-outlined text-3xl">arrow_upward</span>
                    <span class="text-xs font-medium">Lên trên</span>
                </button>

                {{-- Folders --}}
                <template x-for="f in folders" :key="'d' + f.name">
                <div class="relative group border border-gray-200 rounded-lg p-3 flex flex-col items-center justify-center gap-1.5 hover:border-blue-400 hover:shadow-sm transition-all cursor-pointer"
                    @click="enterFolder(f.name)">
                    <span class="material-symbols-outlined text-4xl text-amber-500">folder</span>
                    <span class="text-xs font-medium text-gray-700 text-center break-all leading-tight" x-text="f.name"></span>
                    <span class="text-[10px] text-gray-400" x-text="f.count + ' mục'"></span>
                    <div class="absolute top-1.5 right-1.5 hidden group-hover:flex gap-1" @click.stop>
                        <button type="button" title="Đổi tên" @click="rename(f.name, true)"
                            class="w-7 h-7 rounded bg-white border border-gray-200 text-gray-500 hover:text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[15px]">edit</span>
                        </button>
                        <button type="button" title="Xóa" @click="remove(f.name, true)"
                            class="w-7 h-7 rounded bg-white border border-gray-200 text-gray-500 hover:text-red-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[15px]">delete</span>
                        </button>
                    </div>
                </div>
                </template>

                {{-- Files --}}
                <template x-for="file in files" :key="'f' + file.name">
                <div class="relative group border border-gray-200 rounded-lg overflow-hidden hover:border-blue-400 hover:shadow-sm transition-all {{ $mode === 'picker' ? 'cursor-pointer' : '' }}"
                    @click="{{ $mode === 'picker' ? 'choose(file)' : '' }}">
                    <div class="h-24 bg-gray-50 flex items-center justify-center">
                        <img x-show="file.is_image" :src="file.url" :alt="file.name" x-cloak class="w-full h-full object-cover">
                        <span x-show="!file.is_image" x-cloak class="material-symbols-outlined text-4xl text-slate-400">description</span>
                    </div>
                    <div class="p-2">
                        <div class="text-xs font-medium text-gray-700 truncate" :title="file.name" x-text="file.name"></div>
                        <div class="text-[10px] text-gray-400 flex items-center justify-between">
                            <span x-text="file.size_label"></span>
                            <span x-text="file.modified"></span>
                        </div>
                    </div>
                    <div class="absolute top-1.5 right-1.5 hidden group-hover:flex gap-1">
                        @if($mode === 'picker')
                            <button type="button" title="Chọn file này" @click.stop="choose(file)"
                                class="w-7 h-7 rounded bg-blue-600 text-white flex items-center justify-center hover:bg-blue-700">
                                <span class="material-symbols-outlined text-[15px]">check</span>
                            </button>
                        @endif
                        <button type="button" title="Đổi tên" @click.stop="rename(file.name, false)"
                            class="w-7 h-7 rounded bg-white border border-gray-200 text-gray-500 hover:text-blue-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[15px]">edit</span>
                        </button>
                        <button type="button" title="Xóa" @click.stop="remove(file.name, false)"
                            class="w-7 h-7 rounded bg-white border border-gray-200 text-gray-500 hover:text-red-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[15px]">delete</span>
                        </button>
                    </div>
                </div>
                </template>
            </div>

            <div x-show="folders.length === 0 && files.length === 0" class="py-16 text-center text-gray-400 flex flex-col items-center gap-2">
                <span class="material-symbols-outlined text-5xl">folder_open</span>
                <p class="text-sm">Thư mục này đang trống. Hãy tải lên file hoặc tạo thư mục mới.</p>
            </div>
        </div>
    </div>

    {{-- Picker hint --}}
    <p x-show="{{ $mode === 'picker' ? 'true' : 'false' }}" x-cloak class="text-xs text-gray-500 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[14px]">info</span>
        Nhấp vào một file để chọn — đường dẫn sẽ được điền vào form và cửa sổ tự đóng.
    </p>

    {{-- Hint kéo thả --}}
    <p class="text-xs text-gray-400 flex items-center gap-1.5">
        <span class="material-symbols-outlined text-[14px]">drag_indicator</span>
        Kéo &amp; thả file vào đây để tải lên nhiều file cùng lúc, hoặc dùng nút <strong>Tải lên</strong> ở trên.
    </p>
</div>
