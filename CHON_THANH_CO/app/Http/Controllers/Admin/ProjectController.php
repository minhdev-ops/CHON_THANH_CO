<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => Project::query();

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('slug', 'like', "%{$search}%")
                        ->orWhere('period', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.projects.rows',
                itemsVar: 'projects',
                sortable: [
                    'id' => 'id',
                    'name' => 'name',
                    'period' => 'period',
                    'sort_order' => 'sort_order',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $projects = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.form', [
            'project' => null,
            'products' => Product::all(),
        ]);
    }

    public function store(Request $request)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $project = Project::create([
            'slug' => $this->makeSlug($data),
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'period' => $data['period'],
            'area' => $data['area'] ?? null,
            'hero_image' => $data['hero_image'],
            'desc_image' => $data['desc_image'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->saveRelations($project, $data);

        return redirect()->route('admin.projects.index')->with('success', 'Đã tạo dự án.');
    }

    public function edit(Project $project)
    {
        return view('admin.projects.form', [
            'project' => $project->load('materials', 'images'),
            'products' => Product::all(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $project->update([
            'slug' => $this->makeSlug($data, $project),
            'name' => $data['name'],
            'location' => $data['location'],
            'description' => $data['description'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'period' => $data['period'],
            'area' => $data['area'] ?? null,
            'hero_image' => $data['hero_image'],
            'desc_image' => $data['desc_image'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->saveRelations($project, $data);

        return redirect()->route('admin.projects.index')->with('success', 'Đã cập nhật dự án.');
    }

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('admin.projects.index')->with('success', 'Đã xóa dự án.');
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:delete,activate,deactivate,feature,unfeature'],
        ]);

        $models = Project::whereIn('id', $data['ids'])->get();
        $count = $models->count();

        match ($data['action']) {
            'delete' => $models->each->delete(),
            'activate' => $models->each(fn (Project $model) => $model->update(['is_active' => true])),
            'deactivate' => $models->each(fn (Project $model) => $model->update(['is_active' => false])),
            'feature' => $models->each(fn (Project $model) => $model->update(['is_featured' => true])),
            'unfeature' => $models->each(fn (Project $model) => $model->update(['is_featured' => false])),
        };

        $messages = [
            'delete' => "Đã xóa {$count} dự án.",
            'activate' => "Đã hiển thị {$count} dự án.",
            'deactivate' => "Đã ẩn {$count} dự án.",
            'feature' => "Đã đánh dấu {$count} dự án là tiêu biểu.",
            'unfeature' => "Đã bỏ đánh dấu {$count} dự án tiêu biểu.",
        ];

        return redirect()->route('admin.projects.index')->with('success', $messages[$data['action']]);
    }

    private function saveRelations(Project $project, array $data): void
    {
        $project->materials()->delete();
        foreach (($data['materials'] ?? []) as $row) {
            if (empty($row['name'])) {
                continue;
            }
            $project->materials()->create([
                'product_id' => $row['product_id'] ?: null,
                'image' => $row['image'] ?? '',
                'name' => $row['name'],
                'detail' => $row['detail'] ?? null,
                'sort_order' => $row['sort_order'] ?? 0,
            ]);
        }

        $project->images()->delete();
        foreach (($data['gallery'] ?? []) as $row) {
            if (empty($row['image'])) {
                continue;
            }
            $project->images()->create([
                'image' => $row['image'],
                'alt' => $row['alt'] ?? null,
                'sort_order' => $row['sort_order'] ?? 0,
            ]);
        }
    }

    private function mergeViTranslations(Request $request): void
    {
        $vi = $request->input('translations.vi', []);
        if (! is_array($vi)) {
            return;
        }

        foreach (['name', 'location', 'description', 'meta_title', 'meta_description'] as $key) {
            if (! $request->filled($key) && filled($vi[$key] ?? null)) {
                $request->merge([$key => $vi[$key]]);
            }
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'name' => ['required', 'string', 'max:200'],
            'location' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'period' => ['required', 'string', 'max:50'],
            'area' => ['nullable', 'string', 'max:50'],
            'hero_image' => ['required', 'string', 'max:255'],
            'desc_image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'materials' => ['nullable', 'array'],
            'materials.*.product_id' => ['nullable', 'exists:products,id'],
            'materials.*.image' => ['nullable', 'string', 'max:255'],
            'materials.*.name' => ['required', 'string', 'max:150'],
            'materials.*.detail' => ['nullable', 'string'],
            'materials.*.sort_order' => ['nullable', 'integer'],
            'gallery' => ['nullable', 'array'],
            'gallery.*.image' => ['nullable', 'string', 'max:255'],
            'gallery.*.alt' => ['nullable', 'string', 'max:255'],
            'gallery.*.sort_order' => ['nullable', 'integer'],
        ]);
    }

    private function makeSlug(array $data, ?Project $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'project-'.Str::random(6));
    }
}
