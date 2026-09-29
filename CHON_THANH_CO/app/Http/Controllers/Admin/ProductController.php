<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ProductsExport;
use App\Exports\ProductsTemplateExport;
use App\Http\Controllers\Controller;
use App\Imports\ProductsImport;
use App\Models\Application;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;

class ProductController extends Controller
{
    use Concerns\RendersAdminTables;

    public function index(Request $request)
    {
        $baseQuery = fn () => Product::with('category')->withCount('specs');

        $applyFilters = function ($query, Request $request) {
            if ($request->filled('q')) {
                $search = $request->input('q');
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%");
                });
            }

            if ($request->filled('category')) {
                $query->where('category_id', $request->input('category'));
            }
        };

        if ($this->wantsTableAjax($request)) {
            return $this->tableJson(
                $request,
                $baseQuery,
                $applyFilters,
                'admin.products.rows',
                sortable: [
                    'id' => 'id',
                    'name' => 'name',
                    'category_id' => 'category_id',
                    'strength_min' => 'strength_min',
                    'specs_count' => 'specs_count',
                    'is_featured' => 'is_featured',
                    'is_active' => 'is_active',
                ],
                defaultOrder: ['category_id' => 'asc', 'sort_order' => 'asc'],
            );
        }

        $query = $baseQuery();
        $applyFilters($query, $request);

        $products = $query->orderBy('category_id')->orderBy('sort_order')->paginate(15)->withQueryString();
        $categories = Category::all();

        return view('admin.products.index', compact('products', 'categories'));
    }

    public function create()
    {
        return view('admin.products.form', [
            'product' => null,
            'categories' => Category::all(),
            'applications' => Application::all(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $product = Product::create([
            'category_id' => $data['category_id'],
            'slug' => $this->makeSlug($data),
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'strength_label' => $data['strength_label'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'image' => $data['image'],
            'strength_min' => $data['strength_min'] ?? null,
            'strength_max' => $data['strength_max'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->saveRelations($product, $data);

        return redirect()->route('admin.products.index')->with('success', 'Đã tạo sản phẩm.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product->load('specs', 'images', 'applications'),
            'categories' => Category::all(),
            'applications' => Application::all(),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product);
        $product->update([
            'category_id' => $data['category_id'],
            'slug' => $this->makeSlug($data, $product),
            'code' => $data['code'],
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'strength_label' => $data['strength_label'] ?? null,
            'meta_title' => $data['meta_title'] ?? null,
            'meta_description' => $data['meta_description'] ?? null,
            'image' => $data['image'],
            'strength_min' => $data['strength_min'] ?? null,
            'strength_max' => $data['strength_max'] ?? null,
            'is_featured' => $request->boolean('is_featured'),
            'is_active' => $request->boolean('is_active'),
            'sort_order' => $data['sort_order'] ?? 0,
        ]);

        $this->saveRelations($product, $data);

        return redirect()->route('admin.products.index')->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('admin.products.index')->with('success', 'Đã xóa sản phẩm.');
    }

    public function export()
    {
        return Excel::download(new ProductsExport, 'san-pham-'.date('Y-m-d').'.xlsx');
    }

    public function template()
    {
        return Excel::download(new ProductsTemplateExport, 'mau-import-san-pham.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xlsx,xls,csv'],
        ]);

        $import = new ProductsImport;
        $import->import($request->file('file'));

        $message = "Đã import {$import->imported} sản phẩm (tạo mới: {$import->created}, cập nhật: {$import->updated}).";

        if (count($import->errors) > 0) {
            $message .= ' Lỗi '.count($import->errors).' dòng: '.implode(' | ', array_slice($import->errors, 0, 12));
        }

        return redirect()->route('admin.products.index')
            ->with('success', $message);
    }

    public function bulk(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:delete,activate,deactivate,feature,unfeature'],
        ]);

        $models = Product::whereIn('id', $data['ids'])->get();
        $count = $models->count();

        match ($data['action']) {
            'delete' => $models->each->delete(),
            'activate' => $models->each(fn (Product $model) => $model->update(['is_active' => true])),
            'deactivate' => $models->each(fn (Product $model) => $model->update(['is_active' => false])),
            'feature' => $models->each(fn (Product $model) => $model->update(['is_featured' => true])),
            'unfeature' => $models->each(fn (Product $model) => $model->update(['is_featured' => false])),
        };

        $messages = [
            'delete' => "Đã xóa {$count} sản phẩm.",
            'activate' => "Đã hiển thị {$count} sản phẩm.",
            'deactivate' => "Đã ẩn {$count} sản phẩm.",
            'feature' => "Đã đánh dấu {$count} sản phẩm là nổi bật.",
            'unfeature' => "Đã bỏ đánh dấu {$count} sản phẩm nổi bật.",
        ];

        return redirect()->route('admin.products.index')->with('success', $messages[$data['action']]);
    }

    private function saveRelations(Product $product, array $data): void
    {
        $product->applications()->sync($data['applications'] ?? []);

        $product->specs()->delete();
        foreach (($data['specs'] ?? []) as $row) {
            if (empty($row['value'])) {
                continue;
            }
            $product->specs()->create([
                'icon' => $row['icon'] ?? null,
                'value' => $row['value'],
                'label' => $row['label'] ?? null,
                'sort_order' => $row['sort_order'] ?? 0,
            ]);
        }

        $product->images()->delete();
        foreach (($data['images'] ?? []) as $row) {
            if (empty($row['image'])) {
                continue;
            }
            $product->images()->create([
                'image' => $row['image'],
                'alt' => $row['alt'] ?? null,
                'sort_order' => $row['sort_order'] ?? 0,
            ]);
        }
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        return $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'slug' => ['nullable', 'string', 'max:150'],
            'code' => [
                'required',
                'string',
                'max:100',
                Rule::unique('products', 'code')->whereNull('deleted_at')->ignore($product?->id),
            ],
            'name' => ['required', 'string', 'max:200'],
            'description' => ['required', 'string'],
            'strength_label' => ['nullable', 'string', 'max:50'],
            'meta_title' => ['nullable', 'string', 'max:200'],
            'meta_description' => ['nullable', 'string', 'max:300'],
            'image' => ['required', 'string', 'max:255'],
            'strength_min' => ['nullable', 'numeric'],
            'strength_max' => ['nullable', 'numeric'],
            'sort_order' => ['nullable', 'integer'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'applications' => ['nullable', 'array'],
            'applications.*' => ['exists:applications,id'],
            'specs' => ['nullable', 'array'],
            'specs.*.icon' => ['nullable', 'string', 'max:50'],
            'specs.*.value' => ['nullable', 'string', 'max:100'],
            'specs.*.label' => ['nullable', 'string', 'max:150'],
            'specs.*.sort_order' => ['nullable', 'integer'],
            'images' => ['nullable', 'array'],
            'images.*.image' => ['nullable', 'string', 'max:255'],
            'images.*.alt' => ['nullable', 'string', 'max:255'],
            'images.*.sort_order' => ['nullable', 'integer'],
        ]);
    }

    private function makeSlug(array $data, ?Product $existing = null): string
    {
        if (! empty($data['slug'])) {
            return Str::slug($data['slug']);
        }

        $name = $data['name'] ?? '';

        return Str::slug($name) ?: ($existing?->slug ?? 'product-'.Str::random(6));
    }
}
