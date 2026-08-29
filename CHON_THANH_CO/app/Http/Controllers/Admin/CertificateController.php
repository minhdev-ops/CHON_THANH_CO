<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\Certificate;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CertificateController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $certificates = Certificate::with('translations')->orderBy('sort_order')->get();

        return view('admin.certificates.index', compact('certificates'));
    }

    public function create()
    {
        return view('admin.certificates.form', ['certificate' => null]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $certificate = Certificate::create([
            'slug' => $this->makeSlug($data),
            'image' => $data['image'],
            'file' => $data['file'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($certificate, $data['translations'], ['name', 'description']);

        return redirect()->route('admin.certificates.index')->with('success', 'Đã tạo chứng chỉ.');
    }

    public function edit(Certificate $certificate)
    {
        return view('admin.certificates.form', ['certificate' => $certificate->load('translations')]);
    }

    public function update(Request $request, Certificate $certificate)
    {
        $data = $this->validated($request);
        $certificate->update([
            'slug' => $this->makeSlug($data, $certificate),
            'image' => $data['image'],
            'file' => $data['file'] ?? null,
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($certificate, $data['translations'], ['name', 'description']);

        return redirect()->route('admin.certificates.index')->with('success', 'Đã cập nhật chứng chỉ.');
    }

    public function destroy(Certificate $certificate)
    {
        $certificate->delete();

        return redirect()->route('admin.certificates.index')->with('success', 'Đã xóa chứng chỉ.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'slug' => ['nullable', 'string', 'max:150'],
            'image' => ['required', 'string', 'max:255'],
            'file' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.name' => ['required', 'string', 'max:200'],
            'translations.vi.description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string', 'max:200'],
            'translations.en.description' => ['nullable', 'string'],
        ]);
    }

    private function makeSlug(array $data, ?Certificate $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['translations']['vi']['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'certificate-' . Str::random(6));
    }
}
