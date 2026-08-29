@extends('admin.layouts.app')

@section('title', 'Tổng quan')

@section('content')
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        @foreach ($stats as $stat)
            <a href="{{ route($stat['route']) }}"
                class="bg-white border border-gray-200 rounded-lg p-5 hover:shadow-md transition-shadow">
                <div class="flex items-center justify-between mb-3">
                    <span class="material-symbols-outlined text-slate-400">{{ $stat['icon'] }}</span>
                </div>
                <div class="text-2xl font-bold">{{ $stat['value'] }}</div>
                <div class="text-sm text-gray-500">{{ $stat['label'] }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Liên hệ gần đây</h2>
                <a href="{{ route('admin.contacts.index') }}" class="text-sm text-blue-600 hover:underline">Xem tất cả</a>
            </div>
            @if ($latestContacts->isEmpty())
                <p class="text-sm text-gray-500">Chưa có liên hệ nào.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($latestContacts as $contact)
                        <li class="py-3 flex items-start justify-between gap-4">
                            <div class="min-w-0">
                                <div class="font-medium text-sm flex items-center gap-2">
                                    {{ $contact->name }}
                                    @if (!$contact->is_read)
                                        <span class="bg-blue-100 text-blue-700 text-xs px-1.5 py-0.5 rounded">Mới</span>
                                    @endif
                                </div>
                                <div class="text-xs text-gray-500">{{ $contact->phone }} · {{ $contact->email }}</div>
                                <p class="text-xs text-gray-600 mt-1 truncate">{{ $contact->message }}</p>
                            </div>
                            <span class="text-xs text-gray-400 whitespace-nowrap">{{ $contact->created_at->diffForHumans() }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold mb-4">Liên kết nhanh</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="{{ route('admin.products.create') }}" class="flex items-center gap-3 border border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-sm transition-all">
                    <span class="material-symbols-outlined text-blue-600">add_box</span>
                    <div>
                        <div class="text-sm font-semibold">Thêm sản phẩm</div>
                        <div class="text-xs text-gray-500">Tạo sản phẩm mới</div>
                    </div>
                </a>
                <a href="{{ route('admin.projects.create') }}" class="flex items-center gap-3 border border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-sm transition-all">
                    <span class="material-symbols-outlined text-blue-600">add_business</span>
                    <div>
                        <div class="text-sm font-semibold">Thêm dự án</div>
                        <div class="text-xs text-gray-500">Tạo dự án mới</div>
                    </div>
                </a>
                <a href="{{ route('admin.news.create') }}" class="flex items-center gap-3 border border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-sm transition-all">
                    <span class="material-symbols-outlined text-blue-600">post_add</span>
                    <div>
                        <div class="text-sm font-semibold">Thêm tin tức</div>
                        <div class="text-xs text-gray-500">Đăng bài viết mới</div>
                    </div>
                </a>
                <a href="{{ route('admin.settings.edit') }}" class="flex items-center gap-3 border border-gray-200 rounded-lg p-4 hover:border-blue-400 hover:shadow-sm transition-all">
                    <span class="material-symbols-outlined text-blue-600">settings</span>
                    <div>
                        <div class="text-sm font-semibold">Cấu hình website</div>
                        <div class="text-xs text-gray-500">Thông tin công ty, liên hệ</div>
                    </div>
                </a>
            </div>

            @if ($unreadContacts > 0)
                <a href="{{ route('admin.contacts.index') }}" class="mt-4 flex items-center justify-between bg-blue-50 border border-blue-200 rounded-lg p-4 hover:bg-blue-100 transition-colors">
                    <span class="text-sm font-semibold text-blue-800">Có {{ $unreadContacts }} liên hệ chưa xử lý</span>
                    <span class="material-symbols-outlined text-blue-600">mail</span>
                </a>
            @endif
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg p-6 mt-6">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold">Hoạt động gần đây</h2>
            <a href="{{ route('admin.audit-logs.index') }}" class="text-sm text-blue-600 hover:underline">Xem tất cả</a>
        </div>
        @if ($recentLogs->isEmpty())
            <p class="text-sm text-gray-500">Chưa có hoạt động nào.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($recentLogs as $log)
                    <li class="py-3 flex items-start justify-between gap-4">
                        <div class="min-w-0">
                            <div class="font-medium text-sm">
                                {{ $log->actor }}
                                <span class="text-gray-400 font-normal">
                                    @if ($log->action === 'created')
                                        đã tạo
                                    @elseif ($log->action === 'updated')
                                        đã cập nhật
                                    @else
                                        đã xóa
                                    @endif
                                </span>
                                <span class="text-gray-500 font-normal">{{ class_basename($log->model_type) }} #{{ $log->model_id }}</span>
                            </div>
                            <div class="text-xs text-gray-500 mt-0.5">
                                @foreach (($log->changes ?? []) as $section => $values)
                                    @if (is_array($values))
                                        @foreach ($values as $key => $value)
                                            @if (is_scalar($value) && $value !== null && $value !== '')
                                                <span class="inline-block bg-gray-100 rounded px-1.5 py-0.5 mr-1">{{ $key }}: {{ $value }}</span>
                                            @endif
                                        @endforeach
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <span class="text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at->diffForHumans() }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
