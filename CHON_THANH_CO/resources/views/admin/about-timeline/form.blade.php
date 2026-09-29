@extends('admin.layouts.app')

@section('title', 'Mốc lịch sử')

@section('content')
    <div class="max-w-3xl">
        <form method="POST" action="{{ $item ? route('admin.about-timeline.update', $item) : route('admin.about-timeline.store') }}">
            @csrf
            @if ($item)
                @method('PUT')
            @endif

            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">Thông tin chung</h2>

                <x-admin.partials.input name="year" label="Mốc thời gian" :value="$item?->year" required max="100" :hint="'VD: 2005 - Thành lập'" />

                <x-admin.partials.input name="description" label="Mô tả" :value="$item?->description" type="textarea" required />

                <div class="grid grid-cols-2 gap-4">
                    <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$item?->sort_order" min="0" />
                    <div class="pt-7">
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$item?->is_active ?? true" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                    {{ $item ? 'Lưu thay đổi' : 'Tạo mốc' }}
                </button>
                <a href="{{ route('admin.about-timeline.index') }}" class="text-gray-600 hover:underline">Hủy</a>
            </div>
        </form>
    </div>
@endsection