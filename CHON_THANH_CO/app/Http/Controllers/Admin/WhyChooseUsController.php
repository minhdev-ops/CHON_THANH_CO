<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WhyChooseUs;
use Illuminate\Http\Request;

class WhyChooseUsController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => WhyChooseUs::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where('title', 'like', "%{$search}%");
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.why-choose-us.rows',
                itemsVar: 'items',
                sortable: [
                    'id' => 'id',
                    'title' => 'title',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $items = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.why-choose-us.index', compact('items'));
    }

    public function create()
    {
        return view('admin.why-choose-us.form', ['item' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $item = WhyChooseUs::create([
            'icon' => $data['icon'],
            'title' => $data['title'],
            'description' => $data['description'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.why-choose-us.index')->with('success', 'Đã tạo mục.');
    }

    public function edit(WhyChooseUs $item)
    {
        return view('admin.why-choose-us.form', ['item' => $item]);
    }

    public function update(Request $request, WhyChooseUs $item)
    {
        $data = $this->validated($request);
        $item->update([
            'icon' => $data['icon'],
            'title' => $data['title'],
            'description' => $data['description'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.why-choose-us.index')->with('success', 'Đã cập nhật mục.');
    }

    public function destroy(WhyChooseUs $item)
    {
        $item->delete();

        return redirect()->route('admin.why-choose-us.index')->with('success', 'Đã xóa mục.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'icon' => ['required', 'string', 'max:50'],
            'title' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }
}
