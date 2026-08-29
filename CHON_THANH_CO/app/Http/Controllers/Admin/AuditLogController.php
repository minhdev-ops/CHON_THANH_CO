<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public const MODEL_LABELS = [
        'App\Models\AboutTimeline' => 'Mốc lịch sử',
        'App\Models\Category' => 'Danh mục',
        'App\Models\Application' => 'Ứng dụng',
        'App\Models\Product' => 'Sản phẩm',
        'App\Models\Project' => 'Dự án',
        'App\Models\Certificate' => 'Chứng chỉ',
        'App\Models\News' => 'Tin tức',
        'App\Models\NewsCategory' => 'Danh mục tin',
        'App\Models\Faq' => 'FAQ',
        'App\Models\HomeStat' => 'Số liệu năng lực',
        'App\Models\WhyChooseUs' => 'Lý do chọn',
        'App\Models\Banner' => 'Banner & Hero',
        'App\Models\Setting' => 'Cấu hình',
        'App\Models\ContactMessage' => 'Liên hệ',
    ];

    public const ACTION_LABELS = [
        'created' => 'Tạo mới',
        'updated' => 'Cập nhật',
        'deleted' => 'Xóa',
    ];

    public function index(Request $request)
    {
        $query = AuditLog::latest('id');

        if ($request->filled('action')) {
            $query->where('action', $request->input('action'));
        }

        if ($request->filled('model_type')) {
            $query->where('model_type', $request->input('model_type'));
        }

        if ($request->filled('actor')) {
            $query->where('actor', 'like', '%' . $request->input('actor') . '%');
        }

        $logs = $query->paginate(25)->withQueryString();

        return view('admin.audit-logs.index', [
            'logs' => $logs,
            'modelLabels' => self::MODEL_LABELS,
            'actionLabels' => self::ACTION_LABELS,
        ]);
    }
}
