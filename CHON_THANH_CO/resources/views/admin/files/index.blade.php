@extends('admin.layouts.app')

@section('title', 'Quản lý file')

@section('content')
    <p class="text-sm text-gray-500 mb-6">Quản lý hình ảnh và tài liệu của website. Mỗi mục nội dung có thư mục riêng để dễ phân loại.</p>

    @include('admin.files.browser', [
        'mode' => 'full',
        'inputId' => '',
        'initialType' => $initialType ?? 'Images',
        'initialFolder' => $initialFolder ?? '',
        'shortcuts' => $shortcuts,
    ])
@endsection

@push('scripts')
    @include('admin.files.script')
@endpush
