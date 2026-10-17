<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AboutTimeline;
use Illuminate\Http\Request;

class AboutTimelineController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => AboutTimeline::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('year', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.about-timeline.rows',
                itemsVar: 'items',
                sortable: [
                    'id' => 'id',
                    'year' => 'year',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $items = $query->orderBy('sort_order')->paginate(15)->withQueryString();

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
            'year' => $data['year'],
            'description' => $data['description'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.about-timeline.index')->with('success', 'Đã tạo mốc lịch sử.');
    }

    public function edit(AboutTimeline $item)
    {
        return view('admin.about-timeline.form', ['item' => $item]);
    }

    public function update(Request $request, AboutTimeline $item)
    {
        $data = $this->validated($request);
        $item->update([
            'year' => $data['year'],
            'description' => $data['description'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

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
            'year' => ['required', 'string', 'max:100'],
            'description' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }
}
