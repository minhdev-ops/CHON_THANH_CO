<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;

class BannerController extends Controller
{
    use Concerns\RendersAdminTables;

    public const SECTIONS = [
        'hero' => 'Hero trang chủ',
        'cta' => 'Banner CTA (chân trang chủ)',
    ];

    public function index(Request $request)
    {
        $baseQuery = fn () => Banner::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('section', 'like', "%{$search}%");
                });
            }

            if ($request->filled('section')) {
                $query->where('section', $request->input('section'));
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.banners.rows',
                itemsVar: 'banners',
                viewData: ['sectionLabels' => self::SECTIONS],
                sortable: [
                    'id' => 'id',
                    'title' => 'title',
                    'section' => 'section',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['section' => 'asc', 'sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $banners = $query->orderBy('section')->orderBy('sort_order')->paginate(15)->withQueryString();
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
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'text' => $data['text'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'image' => $data['image'] ?? null,
            'link_to' => $data['link_to'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.banners.index')->with('success', 'Đã tạo banner.');
    }

    public function edit(Banner $banner)
    {
        return view('admin.banners.form', ['banner' => $banner, 'sectionLabels' => self::SECTIONS]);
    }

    public function update(Request $request, Banner $banner)
    {
        $data = $this->validated($request);
        $banner->update([
            'section' => $data['section'],
            'title' => $data['title'] ?? null,
            'subtitle' => $data['subtitle'] ?? null,
            'text' => $data['text'] ?? null,
            'button_text' => $data['button_text'] ?? null,
            'image' => $data['image'] ?? null,
            'link_to' => $data['link_to'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

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
            'section' => ['required', 'string', 'in:'.implode(',', array_keys(self::SECTIONS))],
            'title' => ['nullable', 'string', 'max:300'],
            'subtitle' => ['nullable', 'string', 'max:500'],
            'text' => ['nullable', 'string'],
            'button_text' => ['nullable', 'string', 'max:100'],
            'image' => ['nullable', 'string', 'max:255'],
            'link_to' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }
}
