<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Chọn file — CHON THANH Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.9/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-gray-100 text-gray-900 antialiased p-6 min-h-screen">
    <div class="max-w-6xl mx-auto">
        <div class="flex items-center justify-between mb-4">
            <h1 class="font-semibold text-lg flex items-center gap-2">
                <span class="material-symbols-outlined text-blue-600">photo_library</span>
                Chọn file
            </h1>
            <button type="button" onclick="window.close()" class="text-sm text-gray-500 hover:text-gray-800 flex items-center gap-1">
                <span class="material-symbols-outlined text-[18px]">close</span> Đóng
            </button>
        </div>

        @include('admin.files.browser', [
            'mode' => 'picker',
            'inputId' => $inputId,
            'initialType' => $initialType,
            'initialFolder' => $initialFolder,
            'shortcuts' => $shortcuts,
        ])
    </div>

    @include('admin.files.script')
</body>
</html>
