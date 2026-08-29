<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\AboutTimeline;
use Illuminate\Http\Request;

class AboutTimelineController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $items = AboutTimeline::with('translations')->orderBy('sort_order')->get();

        return view('admin.about-timeline.index', compact('items'));
    }

    public function create()
    {
        return view('admin.about-timeline.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = AboutTimeline::create([
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($item, $data['translations'], ['year', 'description']);

        return redirect()->route('admin.about-timeline.index')->with('success', 'Đã tạo mốc lịch sử.');
    }

    public function edit(AboutTimeline $item)
    {
        return view('admin.about-timeline.form', ['item' => $item->load('translations')]);
    }

    public function update(Request $request, AboutTimeline $item)
    {
        $data = $this->validated($request);
        $item->update([
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($item, $data['translations'], ['year', 'description']);

        return redirect()->route('admin.about-timeline.index')->with('success', 'Đã cập nhật mốc lịch sử.');
    }

    public function destroy(AboutTimeline $item)
    {
        $item->delete();

        return redirect()->route('admin.about-timeline.index')->with('success', 'Đã xóa mốc lịch sử.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.year' => ['required', 'string', 'max:100'],
            'translations.vi.description' => ['required', 'string'],
            'translations.en.year' => ['nullable', 'string', 'max:100'],
            'translations.en.description' => ['nullable', 'string'],
        ]);
    }
}
