<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProjectController extends Controller
{
    use SyncsTranslations;

    public function index(Request $request)
    {
        $query = Project::with('translations');

        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('slug', 'like', "%{$search}%")
                    ->orWhere('period', 'like', "%{$search}%")
                    ->orWhereHas('translations', function ($t) use ($search) {
                        $t->where('name', 'like', "%{$search}%");
                    });
            });
        }

        $projects = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.projects.index', compact('projects'));
    }

    public function create()
    {
        return view('admin.projects.form', [
            'project' => null,
            'products' => Product::with('translations')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $project = Project::create([
            'slug' => $this->makeSlug($data),
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
            'project' => $project->load('translations', 'materials.translations', 'images'),
            'products' => Product::with('translations')->get(),
        ]);
    }

    public function update(Request $request, Project $project)
    {
        $data = $this->validated($request);
        $project->update([
            'slug' => $this->makeSlug($data, $project),
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
        $this->syncTranslations($project, $data['translations'], [
            'name', 'location', 'description', 'meta_title', 'meta_description',
        ]);

        $project->materials()->delete();
        foreach (($data['materials'] ?? []) as $row) {
            if (empty($row['name_vi'])) {
                continue;
            }
            $material = $project->materials()->create([
                'product_id' => $row['product_id'] ?: null,
                'image' => $row['image'] ?? '',
                'sort_order' => $row['sort_order'] ?? 0,
            ]);
            $this->syncTranslations($material, [
                'vi' => ['name' => $row['name_vi'] ?? null, 'detail' => $row['detail_vi'] ?? null],
                'en' => ['name' => $row['name_en'] ?? null, 'detail' => $row['detail_en'] ?? null],
            ], ['name', 'detail']);
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

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'period' => ['required', 'string', 'max:50'],
            'area' => ['nullable', 'string', 'max:50'],
            'hero_image' => ['required', 'string', 'max:255'],
            'desc_image' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'translations.vi.name' => ['required', 'string', 'max:200'],
            'translations.vi.location' => ['required', 'string', 'max:200'],
            'translations.vi.description' => ['required', 'string'],
            'translations.vi.meta_title' => ['nullable', 'string', 'max:200'],
            'translations.vi.meta_description' => ['nullable', 'string', 'max:300'],
            'translations.en.name' => ['nullable', 'string', 'max:200'],
            'translations.en.location' => ['nullable', 'string', 'max:200'],
            'translations.en.description' => ['nullable', 'string'],
            'translations.en.meta_title' => ['nullable', 'string', 'max:200'],
            'translations.en.meta_description' => ['nullable', 'string', 'max:300'],
            'materials' => ['nullable', 'array'],
            'materials.*.product_id' => ['nullable', 'exists:products,id'],
            'materials.*.image' => ['nullable', 'string', 'max:255'],
            'materials.*.name_vi' => ['nullable', 'string', 'max:150'],
            'materials.*.detail_vi' => ['nullable', 'string'],
            'materials.*.name_en' => ['nullable', 'string', 'max:150'],
            'materials.*.detail_en' => ['nullable', 'string'],
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

        $name = $data['translations']['vi']['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'project-'.Str::random(6));
    }
}
