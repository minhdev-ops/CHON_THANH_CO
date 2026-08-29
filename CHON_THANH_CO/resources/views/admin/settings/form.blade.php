@extends('admin.layouts.app')

@section('title', 'Cấu hình')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" class="max-w-3xl">
        @csrf
        @method('PUT')

        @foreach ($groups as $groupKey => $groupLabel)
            <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
                <h2 class="font-semibold mb-4">{{ $groupLabel }}</h2>

                @php
                    $groupSettings = $settings->filter(fn($s) => $s->group === $groupKey);
                @endphp

                @if ($groupSettings->isEmpty())
                    <p class="text-sm text-gray-500">Không có cấu hình nào.</p>
                @else
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach ($groupSettings as $setting)
                            @php
                                $label = \App\Http\Controllers\Admin\SettingController::LABELS[$setting->key] ?? $setting->key;
                                $isLong = in_array($setting->key, ['company.description', 'contact.map_embed', 'about.history_vi', 'about.history_en', 'about.mission_vi', 'about.mission_en', 'about.vision_vi', 'about.vision_en'], true);
                                $isJson = in_array($setting->key, \App\Http\Controllers\Admin\SettingController::JSON_KEYS, true);
                                $isFile = in_array($setting->key, \App\Http\Controllers\Admin\SettingController::FILE_KEYS, true);
                            @endphp
                            <div class="{{ $isLong || $isJson || $isFile ? 'md:col-span-2' : '' }}">
                                @if ($isFile)
                                    <x-admin.partials.file-picker
                                        name="settings.{{ $setting->key }}"
                                        :fieldName="'settings[' . $setting->key . ']'"
                                        label="{{ $label }}"
                                        :value="$setting->value"
                                        :hint="'Upload file PDF. Khách xem trực tiếp trên website (không cần tải về).'"
                                    />
                                @else
                                <label for="setting-{{ $setting->key }}" class="block text-sm font-semibold mb-1.5">{{ $label }}</label>
                                @if ($isJson)
                                    <div class="flex gap-2">
                                        <textarea id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]" rows="6" spellcheck="false"
                                            class="flex-1 font-mono text-xs border border-gray-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        <div class="flex flex-col justify-start gap-2">
                                            <button type="button" data-json-check="setting-{{ $setting->key }}"
                                                class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold px-3 py-2 rounded whitespace-nowrap transition-colors">
                                                Kiểm tra JSON
                                            </button>
                                            <p class="text-[11px] text-gray-400 leading-snug max-w-[110px]">Mỗi dòng một nhà máy: {"name": "...", "location": "...", "product": "..."}</p>
                                        </div>
                                    </div>
                                @elseif ($isLong)
                                    <textarea id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]" rows="3"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                @else
                                    <input id="setting-{{ $setting->key }}" name="settings[{{ $setting->key }}]" type="text" value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                @endif
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @endforeach

        <div class="bg-white border border-gray-200 rounded-lg p-6 mb-6">
            <h2 class="font-semibold mb-1">Thêm khóa cấu hình mới</h2>
            <p class="text-sm text-gray-500 mb-4">Điền 3 ô dưới rồi bấm <strong>Lưu cấu hình</strong>. Khóa mới sẽ xuất hiện trong nhóm tương ứng (tiền tố).</p>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="new_group" class="block text-sm font-semibold mb-1.5">Nhóm</label>
                    <select id="new_group" name="new_group"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @foreach ($groups as $groupKey => $groupLabel)
                            <option value="{{ $groupKey }}">{{ $groupLabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="new_key" class="block text-sm font-semibold mb-1.5">Tên khóa (chữ thường, không dấu)</label>
                    <input id="new_key" name="new_key" type="text" placeholder="vd: tiktok"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label for="new_value" class="block text-sm font-semibold mb-1.5">Giá trị</label>
                    <input id="new_value" name="new_value" type="text"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 mb-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">Lưu cấu hình</button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        // Nút "Kiểm tra JSON" — xác nhận ô JSON hợp lệ trước khi lưu
        document.querySelectorAll('[data-json-check]').forEach((btn) => {
            btn.addEventListener('click', function () {
                const textarea = document.getElementById(this.dataset.jsonCheck);
                if (!textarea) return;

                const value = textarea.value.trim();
                if (value === '') {
                    alert('Ô đang trống — bỏ qua kiểm tra.');
                    return;
                }

                try {
                    JSON.parse(value);
                    alert('✅ JSON hợp lệ.');
                } catch (e) {
                    alert('❌ JSON không hợp lệ:\n\n' + e.message);
                }
            });
        });
    </script>
@endpush
