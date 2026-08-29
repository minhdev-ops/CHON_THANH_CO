<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApplicationResource;
use App\Http\Resources\CategoryResource;
use App\Services\CatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function __construct(protected CatalogService $service) {}

    public function categories(Request $request): JsonResponse
    {
        return CategoryResource::collection($this->service->categories(app()->getLocale()))
            ->response()
            ->setStatusCode(200);
    }

    public function applications(Request $request): JsonResponse
    {
        return ApplicationResource::collection($this->service->applications(app()->getLocale()))
            ->response()
            ->setStatusCode(200);
    }
}
