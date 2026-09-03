<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Đăng nhập — CHON THANH Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
</head>

<body class="bg-[#f8fafc] min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-sm">
        <div class="flex flex-col items-center mb-8">
            <img src="/images/logo.svg" alt="CHON THANH" class="h-16 w-auto object-contain mb-4">
            <h1 class="text-gray-900 font-bold text-2xl">CHON THANH Admin</h1>
            <p class="text-gray-500 text-sm mt-1">Đăng nhập để quản trị website</p>
        </div>

        <form method="POST" action="{{ route('admin.login.submit') }}"
            class="bg-white rounded-xl shadow-sm border border-gray-100 p-8">
            @csrf
            @if ($errors->any())
                <div class="mb-5 bg-red-50 border border-red-200 text-red-700 rounded-lg p-3 text-sm">
                    {{ $errors->first() }}
                </div>
            @endif
            <div class="mb-5">
                <label for="username" class="block text-sm font-semibold text-gray-700 mb-1.5">Tên đăng nhập</label>
                <input id="username" name="username" type="text" value="{{ old('username') }}" required autofocus
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-gray-50 focus:bg-white">
            </div>
            <div class="mb-6">
                <label for="password" class="block text-sm font-semibold text-gray-700 mb-1.5">Mật khẩu</label>
                <input id="password" name="password" type="password" required
                    class="w-full border border-gray-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-primary/20 focus:border-primary transition-colors bg-gray-50 focus:bg-white">
            </div>
            <button type="submit"
                class="w-full bg-primary hover:opacity-90 text-white font-semibold py-3 rounded-lg transition-all shadow-sm hover:shadow active:scale-[0.98]">
                Đăng nhập
            </button>
        </form>
    </div>
</body>

</html>