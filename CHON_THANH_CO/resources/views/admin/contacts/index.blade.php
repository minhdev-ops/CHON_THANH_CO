@extends('admin.layouts.app')

@section('title', 'Liên hệ')

@section('content')
    <div class="flex items-center justify-between mb-6 gap-4 flex-wrap">
        <p class="text-sm text-gray-500">Tin nhắn từ khách hàng gửi qua form liên hệ trên website.</p>

        <form method="GET" action="{{ route('admin.contacts.index') }}" class="flex items-center gap-2">
            <select name="status" onchange="this.form.submit()"
                class="border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="">Tất cả trạng thái</option>
                <option value="new" {{ request('status') === 'new' ? 'selected' : '' }}>Mới</option>
                <option value="replied" {{ request('status') === 'replied' ? 'selected' : '' }}>Đã xử lý</option>
            </select>
            @if (request('status'))
                <a href="{{ route('admin.contacts.index') }}" class="text-sm text-gray-500 hover:underline">Xóa lọc</a>
            @endif
        </form>
    </div>

    <div class="space-y-4">
        @forelse ($contacts as $contact)
            <div class="bg-white border border-gray-200 rounded-lg p-5 {{ $contact->status === 'new' ? 'border-l-4 border-l-blue-500' : '' }}">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="font-semibold">{{ $contact->name }}</span>
                            @if ($contact->company)
                                <span class="text-gray-500 text-sm">· {{ $contact->company }}</span>
                            @endif
                            @if ($contact->status === 'new')
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded">Mới</span>
                            @else
                                <span class="bg-green-100 text-green-700 text-xs px-2 py-0.5 rounded">Đã xử lý</span>
                            @endif
                            <span class="text-xs text-gray-400">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div class="text-sm text-gray-600 mt-1">
                            <a href="tel:{{ $contact->phone }}" class="hover:underline">{{ $contact->phone }}</a>
                            <span class="mx-1">·</span>
                            <a href="mailto:{{ $contact->email }}" class="hover:underline">{{ $contact->email }}</a>
                        </div>
                        @if ($contact->products && count($contact->products))
                            <div class="text-sm text-gray-500 mt-1">Sản phẩm quan tâm:
                                <span class="font-medium">
                                    {{ $contact->product }}
                                </span>
                            </div>
                        @elseif ($contact->product)
                            <div class="text-sm text-gray-500 mt-1">Sản phẩm quan tâm: <span class="font-medium">{{ $contact->product }}</span></div>
                        @endif
                        <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $contact->message }}</p>
                    </div>
                    <div class="flex items-center gap-3 flex-shrink-0">
                        <a href="{{ route('admin.contacts.show', $contact) }}" class="text-blue-600 hover:underline text-sm">Xem</a>
                        @if ($contact->status === 'new')
                            <form method="POST" action="{{ route('admin.contacts.read', $contact) }}">
                                @csrf
                                <button type="submit" class="text-green-600 hover:underline text-sm">Đánh dấu xử lý</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('admin.contacts.destroy', $contact) }}" onsubmit="return confirm('Xóa liên hệ này?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline text-sm">Xóa</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-white border border-gray-200 rounded-lg p-10 text-center text-gray-500">Chưa có liên hệ nào.</div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $contacts->links() }}
    </div>
@endsection
