<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    /** Nhãn tiếng Việt thân thiện cho từng khóa cấu hình. */
    public const LABELS = [
        // Công ty
        'company.name_vi' => 'Tên công ty (Tiếng Việt)',
        'company.name_en' => 'Tên công ty (Tiếng Anh)',
        'company.short_name' => 'Tên viết tắt',
        'company.tax_code' => 'Mã số thuế',
        'company.established' => 'Ngày thành lập',
        'company.capital' => 'Vốn điều lệ',
        'company.director' => 'Giám đốc',
        'company.staff' => 'Số nhân sự',
        'company.address' => 'Địa chỉ trụ sở',
        'company.phone' => 'Điện thoại',
        'company.fax' => 'Fax',
        'company.email' => 'Email công ty',
        'company.website' => 'Website',
        'company.partner' => 'Đối tác',
        'company.brand' => 'Thương hiệu',
        'company.industries' => 'Ngành nghề kinh doanh',
        'company.description' => 'Mô tả công ty',
        'company.factories' => 'Danh sách nhà máy (JSON)',
        'company.capability_file' => 'Hồ sơ năng lực (PDF)',
        'company.inspection_file' => 'Hồ sơ kiểm định (PDF)',
        // Liên hệ
        'contact.address' => 'Địa chỉ',
        'contact.phone' => 'Số điện thoại',
        'contact.fax' => 'Số fax',
        'contact.email' => 'Email',
        'contact.website' => 'Website',
        'contact.contact_email' => 'Email nhận thông báo liên hệ',
        'contact.map_embed' => 'Bản đồ Google Maps (iframe embed)',
        'contact.working_hours' => 'Giờ làm việc',
        // Mạng xã hội
        'social.facebook' => 'Facebook (URL)',
        'social.zalo' => 'Zalo (URL)',
        'social.messenger' => 'Messenger (URL)',
        'social.ggmap' => 'Google Maps (URL chia sẻ)',
        // SEO
        'seo.default_title' => 'Tiêu đề mặc định (SEO)',
        'seo.default_description' => 'Mô tả mặc định (SEO)',
        // Trang giới thiệu
        'about.history_vi' => 'Lịch sử công ty (Tiếng Việt)',
        'about.history_en' => 'Lịch sử công ty (Tiếng Anh)',
        'about.mission_vi' => 'Sứ mệnh (Tiếng Việt)',
        'about.mission_en' => 'Sứ mệnh (Tiếng Anh)',
        'about.vision_vi' => 'Tầm nhìn (Tiếng Việt)',
        'about.vision_en' => 'Tầm nhìn (Tiếng Anh)',
    ];

    /** Các nhóm cấu hình (prefix của key). */
    public const GROUPS = [
        'company' => 'Thông tin công ty',
        'about' => 'Trang giới thiệu',
        'contact' => 'Thông tin liên hệ',
        'social' => 'Mạng xã hội',
        'seo' => 'SEO',
    ];

    /** Các khóa lưu giá trị JSON — hiển thị textarea + validate JSON hợp lệ. */
    public const JSON_KEYS = ['company.factories'];

    /** Các khóa là file PDF (hồ sơ) — hiển thị nút Chọn file + upload lên thư viện. */
    public const FILE_KEYS = ['company.capability_file', 'company.inspection_file'];

    public function edit()
    {
        $groups = self::GROUPS;

        $settings = Setting::orderBy('group')->get()->keyBy('key');

        return view('admin.settings.form', compact('groups', 'settings'));
    }

    public function update(Request $request)
    {
        $settingsInput = $request->input('settings', []);

        $rules = [
            'settings' => ['required', 'array'],
            'settings.*' => ['nullable', 'string', 'max:5000'],
        ];

        // Validate JSON hợp lệ cho các khóa đặc biệt.
        // Lưu ý: key có thể chứa dấu chấm (vd 'company.factories') nên không
        // dùng dot-notation của Laravel (settings.{$key}) — phải đọc trực tiếp.
        foreach (self::JSON_KEYS as $key) {
            if (array_key_exists($key, $settingsInput) && $settingsInput[$key] !== '') {
                json_decode($settingsInput[$key], true);

                if (json_last_error() !== JSON_ERROR_NONE) {
                    return back()
                        ->withInput()
                        ->withErrors(['settings.'.$key => 'Phải là chuỗi JSON hợp lệ.']);
                }
            }
        }

        $request->validate($rules);

        $allowedKeys = Setting::pluck('key')->all();
        $keys = collect($allowedKeys)->flip()->all();

        foreach ($settingsInput as $key => $value) {
            if (! array_key_exists($key, $keys)) {
                continue;
            }

            $setting = Setting::where('key', $key)->first();

            // Chuẩn hóa JSON: decode rồi encode lại (loại bỏ sai sót định dạng, dấu cách thừa)
            if (in_array($key, self::JSON_KEYS, true) && is_string($value) && $value !== '') {
                $decoded = json_decode($value, true);

                if (json_last_error() === JSON_ERROR_NONE) {
                    $value = json_encode($decoded, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
                }
            }

            $setting?->update(['value' => $value]);
        }

        $this->addNewKey($request);

        return redirect()->route('admin.settings.edit')->with('success', 'Đã lưu cấu hình.');
    }

    /** Thêm khóa cấu hình mới từ form (nếu key hợp lệ và chưa tồn tại). */
    protected function addNewKey(Request $request): void
    {
        $group = (string) $request->input('new_group');
        $key = trim((string) $request->input('new_key'));
        $value = (string) $request->input('new_value');

        if ($key === '' || ! isset(self::GROUPS[$group])) {
            return;
        }

        $fullKey = $group.'.'.$key;

        if (! preg_match('/^[a-z0-9_.-]+$/i', $fullKey)) {
            return;
        }

        if (Setting::where('key', $fullKey)->exists()) {
            return;
        }

        Setting::create([
            'key' => $fullKey,
            'value' => $value,
            'group' => $group,
        ]);
    }
}
