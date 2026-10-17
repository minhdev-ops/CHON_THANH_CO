<?php

namespace App\Http\Resources;

use App\Support\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $detail = $request->route('slug') !== null;

        return [
            'slug' => $this->slug,
            'code' => $this->code,
            'name' => $this->name,
            'image' => MediaUrl::url($this->image),
            'strength_label' => $this->strength_label,
            'strength_min' => $this->strength_min,
            'strength_max' => $this->strength_max,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'applications' => ApplicationResource::collection($this->whenLoaded('applications')),
            $this->mergeWhen($detail, [
                'description' => $this->description,
                'applications_text' => $this->applications_text,
                'functions' => $this->functions,
                'meta_title' => $this->meta_title,
                'meta_description' => $this->meta_description,
                'specs' => ProductSpecResource::collection($this->whenLoaded('specs')),
                'images' => ProductImageResource::collection($this->whenLoaded('images')),
                'documents' => ProductDocumentResource::collection($this->whenLoaded('documents')),

                // Dòng sản phẩm: sản phẩm cha + các biến thể cùng dòng.
                // 'variants' gồm cả chính sản phẩm đang xem nếu có cha,
                // để bảng "các cấp trong dòng" luôn đầy đủ.
                'parent' => $this->whenLoaded('parent') && $this->parent !== null
                    ? ProductVariantResource::make($this->parent)
                    : null,
                'variants' => ProductVariantResource::collection($this->whenLoaded('variants')),
                'strength_grades' => ProductStrengthGradeResource::collection(
                    $this->whenLoaded('strengthGrades')
                ),

                // Sản phẩm liên quan (đã xếp hạng) và công trình đã dùng dòng này.
                'related_products' => ProductVariantResource::collection(
                    $this->whenLoaded('relatedProducts')
                ),
                'projects' => ProjectSummaryResource::collection($this->whenLoaded('lineProjects')),
            ]),
        ];
    }
}
