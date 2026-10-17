<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => Certificate::query();

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
                'admin.certificates.rows',
                itemsVar: 'certificates',
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

        $certificates = $query->orderBy('sort_order')->paginate(15)->withQueryString();

        return view('admin.certificates.index', compact('certificates'));
    }

    public function create()
    {
        return view('admin.certificates.form', ['certificate' => null]);
    }

    public function store(Request $request)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $certificate = Certificate::create([
            'slug' => $this->makeSlug($data),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'],
            'file' => $data['file'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.certificates.index')->with('success', 'Đã tạo chứng chỉ.');
    }

    public function edit(Certificate $certificate)
    {
        return view('admin.certificates.form', ['certificate' => $certificate]);
    }

    public function update(Request $request, Certificate $certificate)
    {
        $this->mergeViTranslations($request);
        $data = $this->validated($request);
        $certificate->update([
            'slug' => $this->makeSlug($data, $certificate),
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'image' => $data['image'],
            'file' => $data['file'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.certificates.index')->with('success', 'Đã cập nhật chứng chỉ.');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return redirect()->route('admin.certificates.index')->with('success', 'Đã xóa chứng chỉ.');
    }

    private function mergeViTranslations(Request $request): void
    {
        $vi = $request->input('translations.vi', []);
        if (! is_array($vi)) {
            return;
        }

        foreach (['name', 'description'] as $key) {
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
            'description' => ['nullable', 'string'],
            'image' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
        ]);
    }

    private function makeSlug(array $data, ?Certificate $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'certificate-'.Str::random(6));
    }
}
