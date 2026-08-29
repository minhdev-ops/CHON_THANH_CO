<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\SyncsTranslations;
use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ApplicationController extends Controller
{
    use SyncsTranslations;

    public function index()
    {
        $applications = Application::with('translations')->withCount('products')->orderBy('sort_order')->get();

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
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($application, $data['translations'], ['name', 'description']);

        return redirect()->route('admin.applications.index')->with('success', 'Đã tạo ứng dụng.');
    }

    public function edit(Application $application)
    {
        return view('admin.applications.form', ['application' => $application->load('translations')]);
    }

    public function update(Request $request, Application $application)
    {
        $data = $this->validated($request);
        $application->update([
            'slug' => $this->makeSlug($data, $application),
            'sort_order' => $data['sort_order'] ?? 0,
            'is_active' => $request->boolean('is_active'),
        ]);

        $this->syncTranslations($application, $data['translations'], ['name', 'description']);

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
            'sort_order' => ['nullable', 'integer'],
            'is_active' => ['boolean'],
            'translations.vi.name' => ['required', 'string', 'max:150'],
            'translations.vi.description' => ['nullable', 'string'],
            'translations.en.name' => ['nullable', 'string', 'max:150'],
            'translations.en.description' => ['nullable', 'string'],
        ]);
    }

    private function makeSlug(array $data, ?Application $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['translations']['vi']['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'application-' . Str::random(6));
    }
}
