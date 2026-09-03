<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Quản trị') — CHON THANH Admin</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-[#f8fafc] text-gray-900 antialiased">
    <div class="min-h-screen flex">
        <aside class="w-64 bg-white text-gray-600 border-r border-gray-200 flex-shrink-0 hidden md:flex flex-col fixed inset-y-0 shadow-sm z-30">
            <div class="h-16 flex items-center gap-3 px-6 border-b border-gray-200">
                <img src="/images/logo.svg" alt="Logo" class="h-10 w-auto object-contain">
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm font-medium">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.dashboard') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">dashboard</span> Tổng quan
                </a>
                <div class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Nội dung</div>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.products.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">inventory_2</span> Sản phẩm
                </a>
                <a href="{{ route('admin.categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.categories.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">category</span> Danh mục
                </a>
                <a href="{{ route('admin.applications.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.applications.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">apps</span> Ứng dụng
                </a>
                <a href="{{ route('admin.projects.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.projects.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">business_center</span> Dự án
                </a>
                <a href="{{ route('admin.certificates.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.certificates.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">verified</span> Chứng chỉ
                </a>
                <a href="{{ route('admin.news.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.news.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">newspaper</span> Tin tức
                </a>
                <a href="{{ route('admin.news-categories.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.news-categories.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">topic</span> Danh mục tin
                </a>
                <a href="{{ route('admin.faqs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.faqs.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">help</span> FAQ
                </a>
                <div class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Trang chủ</div>
                <a href="{{ route('admin.home-stats.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.home-stats.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">monitoring</span> Số liệu năng lực
                </a>
                <a href="{{ route('admin.why-choose-us.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.why-choose-us.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">thumb_up</span> Lý do chọn
                </a>
                <a href="{{ route('admin.banners.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.banners.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">image</span> Banner & Hero
                </a>
                <a href="{{ route('admin.about-timeline.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.about-timeline.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">timeline</span> Mốc lịch sử
                </a>
                <div class="px-3 pt-4 pb-1 text-[11px] font-bold uppercase tracking-wider text-gray-400">Hệ thống</div>
                <a href="{{ route('admin.contacts.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.contacts.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">mail</span> Liên hệ
                </a>
                <a href="{{ route('admin.files.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.files.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">folder</span> Quản lý file
                </a>
                <a href="{{ route('admin.audit-logs.index') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.audit-logs.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">history</span> Nhật ký hoạt động
                </a>
                <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 {{ request()->routeIs('admin.settings.*') ? 'bg-primary/10 text-primary font-semibold' : '' }}">
                    <span class="material-symbols-outlined text-[20px]">settings</span> Cấu hình
                </a>
            </nav>
            <div class="border-t border-gray-200 p-4">
                <a href="/" target="_blank" class="flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900">
                    <span class="material-symbols-outlined text-[20px]">public</span> Xem website
                </a>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded transition-colors hover:bg-gray-100 hover:text-gray-900 mt-1">
                        <span class="material-symbols-outlined text-[20px]">logout</span> Đăng xuất
                    </button>
                </form>
            </div>
        </aside>

        <div class="flex-1 flex flex-col md:ml-64">
            <header class="h-16 bg-white border-b border-gray-200 flex items-center justify-between px-6 sticky top-0 z-20">
                <h1 class="font-semibold text-lg">@yield('title', 'Tổng quan')</h1>
                <div class="flex items-center gap-4">
                    <a href="/" target="_blank" class="text-sm text-gray-500 hover:text-gray-800 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[18px]">open_in_new</span> Website
                    </a>
                    <span class="text-sm text-gray-500">Admin</span>
                </div>
            </header>

            <main class="flex-1 p-6">
                @if (session('success'))
                    <div class="mb-4 flex items-start gap-3 bg-green-50 border border-green-300 text-green-800 rounded p-4 text-sm">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        <span>{{ session('success') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-300 text-red-800 rounded p-4 text-sm">
                        <span class="material-symbols-outlined text-[18px]">error</span>
                        <span>{{ session('error') }}</span>
                    </div>
                @endif
                @if ($errors->any())
                    <div class="mb-4 bg-red-50 border border-red-300 text-red-800 rounded p-4 text-sm">
                        <strong class="block mb-1">Có lỗi xảy ra:</strong>
                        <ul class="list-disc list-inside space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
    <script>
        document.addEventListener('click', function (e) {
            const btn = e.target.closest('[data-finder]');
            if (!btn) return;

            const input = document.getElementById(btn.dataset.finder);
            if (!input) return;

            const type = btn.dataset.type || 'Images';
            const folder = btn.dataset.folder || '';
            const params = new URLSearchParams({ input: btn.dataset.finder, type });
            if (folder) params.set('folder', folder);

            const url = @json(route('admin.files.picker')) + '?' + params.toString();
            const w = window.open(url, 'file_manager_picker', 'width=1060,height=740');

            if (!w) {
                alert('Trình duyệt đã chặn cửa sổ chọn ảnh. Vui lòng cho phép popup (cửa sổ bật lên) cho trang này rồi thử lại.');
            }
        });

        function updateImagePreview(container, url) {
            if (!container) return;
            if (url && !/\.(jpe?g|png|gif|webp|bmp)(\?|#|$)/i.test(url)) {
                container.innerHTML = '<div class="flex items-center gap-2 text-blue-600 text-sm">' +
                    '<span class="material-symbols-outlined text-[18px]">description</span>' +
                    '<a href="' + url + '" target="_blank" class="underline break-all">' + url + '</a></div>';
                return;
            }
            container.innerHTML = '<img src="' + url + '" alt="" class="h-20 w-auto object-contain border border-gray-200 rounded">';
        }
    </script>
    @stack('scripts')
</body>
</html>
