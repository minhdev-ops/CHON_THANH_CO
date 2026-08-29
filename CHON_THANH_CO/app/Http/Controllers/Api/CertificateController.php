<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CertificateResource;
use App\Services\ContentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CertificateController extends Controller
{
    public function __construct(protected ContentService $service) {}

    public function index(Request $request): JsonResponse
    {
        return CertificateResource::collection($this->service->certificates(app()->getLocale()))
            ->response()
            ->setStatusCode(200);
    }
}
