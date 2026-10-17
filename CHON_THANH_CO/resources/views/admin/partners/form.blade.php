@extends('admin.layouts.app')

@section('title', 'Đối tác')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $partner ? route('admin.partners.update', $partner) : route('admin.partners.store') }}">
            @csrf
            @if ($partner)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin đối tác</h2>

                <x-admin.partials.input name="name" label="Tên công ty" :value="$partner?->name" :hint="'VD: HOCK Technology, Petrovietnam...'" required />

                <x-admin.partials.image-picker name="image" label="Logo" :value="$partner?->image" :hint="'Bấm nút Chọn ảnh để upload hoặc chọn từ thư viện. Ưu định PNG/NG và nền trong suốt. Để trống nếu chưa có logo — website sẽ hiển thị tên công ty dạng chữ.'" folder="partners" />

                @if ($partner?->image)
                    <img src="{{ $partner->image }}" alt="" class="h-16 w-auto object-contain mb-4 border border-gray-200 rounded bg-gray-50 px-2 py-1">
                @endif

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$partner?->sort_order" min="0" />
                    <div class="pt-7">
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$partner?->is_active ?? true" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $partner ? 'Lưu thay đổi' : 'Tạo đối tác' }}
                </button>
                <a href="{{ route('admin.partners.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
