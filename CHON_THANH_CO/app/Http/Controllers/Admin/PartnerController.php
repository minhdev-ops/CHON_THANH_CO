<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => Partner::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where('name', 'like', "%{$search}%");
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.partners.rows',
                itemsVar: 'partners',
                sortable: [
                    'id' => 'id',
                    'name' => 'name',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $partners = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.partners.index', compact('partners'));
    }

    public function create()
    {
        return view('admin.partners.form', ['partner' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        Partner::create([
            'name' => $data['name'],
            'image' => $data['image'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Đã tạo đối tác.');
    }

    public function edit(Partner $partner)
    {
        return view('admin.partners.form', ['partner' => $partner]);
    }

    public function update(Request $request, Partner $partner)
    {
        $data = $this->validated($request);
        $partner->update([
            'name' => $data['name'],
            'image' => $data['image'],
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.partners.index')->with('success', 'Đã cập nhật đối tác.');
    }

    public function destroy(Partner $partner)
    {
        $partner->delete();

        return redirect()->route('admin.partners.index')->with('success', 'Đã xóa đối tác.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:delete,activate,deactivate'],
        ]);

        $models = Partner::whereIn('id', $data['ids'])->get();

        foreach ($models as $model) {
            if ($data['action'] === 'delete') {
                $model->delete();
            } elseif ($data['action'] === 'activate') {
                $model->update(['is_active' => true]);
            } else {
                $model->update(['is_active' => false]);
            }
        }

        $messages = ['delete' => 'Đã xóa', 'activate' => 'Đã hiển thị', 'deactivate' => 'Đã ẩn'];

        return back()->with('success', "{$messages[$data['action']]} {$models->count()} đối tác.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:200'],
            'image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }
}
