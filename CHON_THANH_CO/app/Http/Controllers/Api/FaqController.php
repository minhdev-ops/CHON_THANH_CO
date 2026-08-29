<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function __construct(protected ContentService $service) {}

    public function index(Request $request): JsonResponse
    {
        return FaqResource::collection($this->service->faqs(app()->getLocale()))
            ->response()
            ->setStatusCode(200);
    }
}
