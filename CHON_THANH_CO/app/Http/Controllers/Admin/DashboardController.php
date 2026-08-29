<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutTimeline;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Banner;
use App\Models\Category;
use App\Models\Certificate;
use App\Models\ContactMessage;
use App\Models\Faq;
use App\Models\HomeStat;
use App\Models\News;
use App\Models\Product;
use App\Models\Project;
use App\Models\Setting;
use App\Models\WhyChooseUs;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            ['label' => 'Sản phẩm', 'value' => Product::count(), 'icon' => 'inventory_2', 'route' => 'admin.products.index'],
            ['label' => 'Danh mục', 'value' => Category::count(), 'icon' => 'category', 'route' => 'admin.categories.index'],
            ['label' => 'Ứng dụng', 'value' => Application::count(), 'icon' => 'apps', 'route' => 'admin.applications.index'],
            ['label' => 'Dự án', 'value' => Project::count(), 'icon' => 'business_center', 'route' => 'admin.projects.index'],
            ['label' => 'Chứng chỉ', 'value' => Certificate::count(), 'icon' => 'verified', 'route' => 'admin.certificates.index'],
            ['label' => 'Tin tức', 'value' => News::count(), 'icon' => 'newspaper', 'route' => 'admin.news.index'],
            ['label' => 'FAQ', 'value' => Faq::count(), 'icon' => 'help', 'route' => 'admin.faqs.index'],
            ['label' => 'Số liệu năng lực', 'value' => HomeStat::count(), 'icon' => 'monitoring', 'route' => 'admin.home-stats.index'],
            ['label' => 'Lý do chọn', 'value' => WhyChooseUs::count(), 'icon' => 'thumb_up', 'route' => 'admin.why-choose-us.index'],
            ['label' => 'Banner & Hero', 'value' => Banner::count(), 'icon' => 'image', 'route' => 'admin.banners.index'],
            ['label' => 'Mốc lịch sử', 'value' => AboutTimeline::count(), 'icon' => 'timeline', 'route' => 'admin.about-timeline.index'],
            ['label' => 'Cấu hình', 'value' => Setting::count(), 'icon' => 'settings', 'route' => 'admin.settings.edit'],
        ];

        $unreadContacts = ContactMessage::where('status', 'new')->count();
        $latestContacts = ContactMessage::latest()->limit(6)->get();
        $recentLogs = AuditLog::latest('id')->limit(6)->get();

        return view('admin.dashboard', compact('stats', 'unreadContacts', 'latestContacts', 'recentLogs'));
    }
}
