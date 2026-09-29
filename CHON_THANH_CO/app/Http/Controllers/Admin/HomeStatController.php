<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HomeStat;
use Illuminate\Http\Request;

class HomeStatController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => HomeStat::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('label', 'like', "%{$search}%")
                        ->orWhere('value', 'like', "%{$search}%");
                });
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.home-stats.rows',
                itemsVar: 'stats',
                sortable: [
                    'id' => 'id',
                    'label' => 'label',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $stats = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.home-stats.index', compact('stats'));
    }

    public function create()
    {
        return view('admin.home-stats.form', ['stat' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $stat = HomeStat::create([
            'icon' => $data['icon'],
            'value' => $data['value'],
            'label' => $data['label'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.home-stats.index')->with('success', 'Đã tạo số liệu.');
    }

    public function edit(HomeStat $stat)
    {
        return view('admin.home-stats.form', ['stat' => $stat]);
    }

    public function update(Request $request, HomeStat $stat)
    {
        $data = $this->validated($request);
        $stat->update([
            'icon' => $data['icon'],
            'value' => $data['value'],
            'label' => $data['label'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.home-stats.index')->with('success', 'Đã cập nhật số liệu.');
    }

    public function destroy(HomeStat $stat)
    {
        $stat->delete();

        return redirect()->route('admin.home-stats.index')->with('success', 'Đã xóa số liệu.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'icon' => ['required', 'string', 'max:50'],
            'value' => ['required', 'string', 'max:50'],
            'label' => ['required', 'string', 'max:150'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }
}
