<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Services\ProductService;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(protected ProductService $service) {}

    public function index(Request $request)
    {
        $result = $this->service->list(
            $request->only(['category', 'application', 'search', 'strength_min', 'strength_max', 'cursor']),
            app()->getLocale(),
            min(50, max(1, (int) ($request->input('limit') ?: 12))),
        );

        return response()->json([
            'data' => ProductResource::collection($result['items'])->resolve(),
            'next_cursor' => $result['next_cursor'],
        ]);
    }

    public function show(string $slug): ProductResource
    {
        $product = $this->service->findBySlug($slug, app()->getLocale());

        abort_unless($product, 404);

        // Bảng "các cấp trong dòng" phải gồm cả sản phẩm đang xem, nếu nó là
        // một biến thể. Sản phẩm cha thì variants đã là toàn bộ dòng.
        if ($product->parent_id && $product->parent !== null) {
            $siblings = $product->parent->variants()
                ->with('category')
                ->get()
                ->push($product)
                ->unique('id')
                ->sortBy([
                    fn ($a, $b) => ($a->sort_order ?? 0) <=> ($b->sort_order ?? 0),
                    fn ($a, $b) => $a->id <=> $b->id,
                ])
                ->values();

            $product->setRelation('variants', $siblings);
        }

        // Sản phẩm liên quan và công trình tính ở server để trang chi tiết
        // chỉ cần một lần gọi API.
        $product->setRelation('relatedProducts', $this->service->related($product, 8));
        $product->setRelation('lineProjects', $this->service->projectsOfLine($product));

        return ProductResource::make($product);
    }
}
