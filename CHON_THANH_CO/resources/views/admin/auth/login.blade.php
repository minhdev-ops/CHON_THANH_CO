<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — CHON THANH Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <img src="/images/logo.svg" alt="CHON THANH" class="h-12 w-auto mb-4">
            <h1 class="text-white font-bold text-xl">CHON THANH Admin</h1>
            <p class="text-slate-400 text-sm mt-1">Đăng nhập để quản trị website</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}" class="bg-white rounded-lg shadow-xl p-8">
            @csrf
            @if ($errors->any())
                <div class="mb-4 bg-red-50 border border-red-300 text-red-800 rounded p-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif
            <div class="mb-4">
                <label for="username" class="block text-sm font-semibold mb-1.5">Tên đăng nhập</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold mb-1.5">Mật khẩu</label>
                <input id="password" name="password" type="password" required
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <button type="submit"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 rounded transition-colors">
                Đăng nhập
            </button>
        </form>

        <p class="text-center text-slate-500 text-xs mt-6">Thông tin đăng nhập được cấu hình trong file <code>.env</code> (ADMIN_USERNAME / ADMIN_PASSWORD)</p>
    </div>
</body>
</html>
