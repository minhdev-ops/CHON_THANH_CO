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

        return ProductResource::make($product);
    }
}
