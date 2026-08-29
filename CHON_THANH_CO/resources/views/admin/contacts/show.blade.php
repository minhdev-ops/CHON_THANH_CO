@extends('admin.layouts.app')

@section('title', 'Chi tiết liên hệ')

@section('content')
    <a href="{{ route('admin.contacts.index') }}" class="text-sm text-gray-500 hover:underline mb-4 inline-flex items-center gap-1">
        <span class="material-symbols-outlined text-[16px]">arrow_back</span> Quay lại danh sách
    </a>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="font-semibold">Thông tin khách hàng</h2>
                @if ($contact->status === 'new')
                    <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded">Mới</span>
                @else
                    <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded">Đã xử lý</span>
                @endif
            </div>

            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Họ tên</dt>
                    <dd class="font-medium text-right">{{ $contact->name }}</dd>
                </div>
                @if ($contact->company)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Công ty</dt>
                        <dd class="font-medium text-right">{{ $contact->company }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Điện thoại</dt>
                    <dd class="font-medium text-right">
                        <a href="tel:{{ $contact->phone }}" class="text-blue-600 hover:underline">{{ $contact->phone }}</a>
                    </dd>
                </div>
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Email</dt>
                    <dd class="font-medium text-right break-all">
                        <a href="mailto:{{ $contact->email }}" class="text-blue-600 hover:underline">{{ $contact->email }}</a>
                    </dd>
                </div>
                @if ($contact->product)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                        <dt class="text-gray-500">Sản phẩm quan tâm</dt>
                        <dd class="font-medium text-right">{{ $contact->product }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-4 border-b border-gray-100 pb-2">
                    <dt class="text-gray-500">Thời gian gửi</dt>
                    <dd class="font-medium text-right">{{ $contact->created_at->format('H:i d/m/Y') }}</dd>
                </div>
                @if ($contact->handled_at)
                    <div class="flex justify-between gap-4">
                        <dt class="text-gray-500">Xử lý lúc</dt>
                        <dd class="font-medium text-right">{{ $contact->handled_at->format('H:i d/m/Y') }}</dd>
                    </div>
                @endif
            </dl>

            <h3 class="font-semibold mt-6 mb-2">Nội dung</h3>
            <p class="text-sm text-gray-700 whitespace-pre-line bg-gray-50 border border-gray-200 rounded p-4">{{ $contact->message }}</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="font-semibold mb-1">Trả lời qua email</h2>
            <p class="text-sm text-gray-500 mb-4">Email sẽ được gửi tới <strong>{{ $contact->email }}</strong>. Liên hệ sẽ được tự động đánh dấu là đã xử lý.</p>

            <form method="POST" action="{{ route('admin.contacts.reply', $contact) }}">
                @csrf
                <textarea name="reply" rows="10" required
                    placeholder="Nhập nội dung phản hồi cho khách hàng..."
                    class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('reply') }}</textarea>

                @error('reply')
                    <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                @enderror

                <div class="flex items-center gap-3 mt-4">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">send</span> Gửi email
                    </button>
                </div>
            </form>

            @if ($contact->status === 'new')
                <form method="POST" action="{{ route('admin.contacts.read', $contact) }}" class="mt-3">
                    @csrf
                    <button type="submit" class="text-green-600 hover:underline text-sm">Chỉ đánh dấu đã xử lý</button>
                </form>
            @endif
        </div>
    </div>
@endsection
