<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use SyncsTranslations;

    public const SECTIONS = [
        'hero' => 'Hero trang chủ',
        'cta' => 'Banner CTA (chân trang chủ)',
    ];

    public function index()
    {
        $banners = Banner::with('translations')->orderBy('section')->orderBy('sort_order')->get();
        $sectionLabels = self::SECTIONS;

        return view('admin.banners.index', compact('banners', 'sectionLabels'));
    }

    public function create()
    {
        return view('admin.banners.form', ['banner' => null, 'sectionLabels' => self::SECTIONS]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $banner = Banner::create([
            'section' => $data['section'],
            'image' => $data['image'] ?? null,
            'link_to' => $data['link_to'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($banner, $data['translations'], ['title', 'subtitle', 'text', 'button_text']);

        return redirect()->route('admin.banners.index')->with('success', 'Đã tạo banner.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', ['banner' => $banner->load('translations'), 'sectionLabels' => self::SECTIONS]);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request);
        $banner->update([
            'section' => $data['section'],
            'image' => $data['image'] ?? null,
            'link_to' => $data['link_to'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($banner, $data['translations'], ['title', 'subtitle', 'text', 'button_text']);

        return redirect()->route('admin.banners.index')->with('success', 'Đã cập nhật banner.');
    }

    public function destroy(Banner $banner)
    {
        $banner->delete();

        return redirect()->route('admin.banners.index')->with('success', 'Đã xóa banner.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'section' => ['required', 'string', 'in:' . implode(',', array_keys(self::SECTIONS))],
            'image' => ['nullable', 'string', 'max:255'],
            'link_to' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.title' => ['nullable', 'string', 'max:300'],
            'translations.vi.subtitle' => ['nullable', 'string', 'max:500'],
            'translations.vi.text' => ['nullable', 'string'],
            'translations.vi.button_text' => ['nullable', 'string', 'max:100'],
            'translations.en.title' => ['nullable', 'string', 'max:300'],
            'translations.en.subtitle' => ['nullable', 'string', 'max:500'],
            'translations.en.text' => ['nullable', 'string'],
            'translations.en.button_text' => ['nullable', 'string', 'max:100'],
        ]);
    }
}
