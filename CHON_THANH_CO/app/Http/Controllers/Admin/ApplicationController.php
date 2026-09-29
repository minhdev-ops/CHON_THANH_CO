<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => Application::withCount('products');

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.applications.rows',
                itemsVar: 'applications',
                sortable: [
                    'id' => 'id',
                    'name' => 'name',
                    'sort_order' => 'sort_order',
                    'products_count' => 'products_count',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $applications = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.applications.index', compact('applications'));
    }

    public function create()
    {
        return view('admin.applications.form', ['application' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $application = Application::create([
            'slug' => $this->makeSlug($data),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Đã tạo ứng dụng.');
    }

    public function edit(Application $application)
    {
        return view('admin.applications.form', ['application' => $application]);
    }

    public function update(Request $request, Application $application)
    {
        $data = $this->validated($request);
        $application->update([
            'slug' => $this->makeSlug($data, $application),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.applications.index')->with('success', 'Đã cập nhật ứng dụng.');
    }

    public function destroy(Application $application)
    {
        if ($application->products()->exists()) {
            return back()->with('error', 'Không thể xóa ứng dụng đang được gắn với sản phẩm.');
        }

        $application->delete();

        return redirect()->route('admin.applications.index')->with('success', 'Đã xóa ứng dụng.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:150'],
            'description' => ['nullable', 'string'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }

    private function makeSlug(array $data, ?Application $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'application-'.Str::random(6));
    }
}
