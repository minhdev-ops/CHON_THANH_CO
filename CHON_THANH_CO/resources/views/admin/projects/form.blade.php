@extends('admin.layouts.app')

@section('title', 'Dự án')

@section('content')
    @php
        $projectMaterials = $project?->materials ?? collect();
        $projectGallery = $project?->images ?? collect();

        $projectMaterialsData = [];
        foreach ($projectMaterials as $m) {
            $projectMaterialsData[] = [
                'product_id' => $m->product_id,
                'image' => $m->image,
                'sort_order' => $m->sort_order,
                'name_vi' => $m->translation('vi')?->name ?? '',
                'detail_vi' => $m->translation('vi')?->detail ?? '',
                'name_en' => $m->translation('en')?->name ?? '',
                'detail_en' => $m->translation('en')?->detail ?? '',
            ];
        }

        $projectGalleryData = [];
        foreach ($projectGallery as $img) {
            $projectGalleryData[] = [
                'image' => $img->image,
                'alt' => $img->alt,
                'sort_order' => $img->sort_order,
            ];
        }
    @endphp

    <form method="POST" action="{{ $project ? route('admin.projects.update', $project) : route('admin.projects.store') }}"
        x-data="projectForm({ materials: @json($projectMaterialsData), gallery: @json($projectGalleryData) })" @submit="mergeDynamic()">
        @csrf
        @if ($project)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Thông tin chung</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <x-admin.partials.input name="period" label="Thời gian" :value="$project?->period" :hint="'VD: 2024–2025'" required />
                        <x-admin.partials.input name="slug" label="Slug" :value="$project?->slug" :hint="'Để trống để tự tạo từ tên tiếng Việt.'" />
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <x-admin.partials.input name="area" label="Diện tích" :value="$project?->area" :hint="'VD: 120.000 m²'" />
                        <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$project?->sort_order" min="0" />
                    </div>

                    <x-admin.partials.image-picker name="hero_image" label="Ảnh bìa (hero)" :value="$project?->hero_image" folder="projects" required />
                    <x-admin.partials.image-picker name="desc_image" label="Ảnh mô tả (desc_image)" :value="$project?->desc_image" :hint="'Dùng trong phần mô tả chi tiết.'" folder="projects" />

                    <div class="flex flex-wrap gap-6">
                        <x-admin.partials.checkbox name="is_featured" label="Dự án tiêu biểu (hiển thị trên trang chủ)" :checked="$project?->is_featured ?? false" />
                        <x-admin.partials.checkbox name="is_active" label="Hiển thị trên website" :checked="$project?->is_active ?? true" />
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Nội dung đa ngôn ngữ</h2>
                    <div class="flex gap-2 mb-4">
                        <button type="button" @click="lang = 'vi'" :class="lang === 'vi' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-4 py-1.5 rounded text-sm font-semibold">Tiếng Việt</button>
                        <button type="button" @click="lang = 'en'" :class="lang === 'en' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700'" class="px-4 py-1.5 rounded text-sm font-semibold">English</button>
                    </div>

                    <x-admin.partials.translation-section
                        locale="vi"
                        label="Tiếng Việt"
                        :fields="[
                            ['name' => 'name', 'label' => 'Tên dự án', 'required' => true, 'max' => 200],
                            ['name' => 'location', 'label' => 'Địa điểm', 'required' => true, 'max' => 200],
                            ['name' => 'description', 'label' => 'Mô tả', 'type' => 'textarea', 'required' => true, 'rows' => 8],
                            ['name' => 'meta_title', 'label' => 'Meta title (SEO)', 'max' => 200],
                            ['name' => 'meta_description', 'label' => 'Meta description (SEO)', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                        ]"
                        :values="$project?->translation('vi')?->toArray() ?? []"
                    />

                    <x-admin.partials.translation-section
                        locale="en"
                        label="English"
                        :fields="[
                            ['name' => 'name', 'label' => 'Project name', 'max' => 200],
                            ['name' => 'location', 'label' => 'Location', 'max' => 200],
                            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 8],
                            ['name' => 'meta_title', 'label' => 'Meta title', 'max' => 200],
                            ['name' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                        ]"
                        :values="$project?->translation('en')?->toArray() ?? []"
                    />
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Vật liệu sử dụng</h2>
                        <button type="button" @click="addMaterial()" class="text-blue-600 hover:underline text-sm">+ Thêm</button>
                    </div>

                    <template x-for="(mat, i) in materials" :key="i">
                        <div class="border border-gray-200 rounded p-3 mb-3 bg-gray-50">
                            <div class="flex items-start gap-2 mb-2">
                                <select x-model="mat.product_id" class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="">-- Không liên kết sản phẩm --</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->translation('vi')?->name }} ({{ $product->code }})</option>
                                    @endforeach
                                </select>
                                <div class="w-20">
                                    <label class="block text-xs font-semibold mb-1">Thứ tự</label>
                                    <input x-model="mat.sort_order" type="number"
                                        class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                </div>
                                <button type="button" @click="materials.splice(i, 1)" class="text-red-500 mt-6">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                            <div class="flex gap-2">
                                <input :id="'x-mat-img-' + i" x-model="mat.image" type="text" placeholder="Đường dẫn ảnh vật liệu"
                                    class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm mb-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <button type="button" :data-finder="'x-mat-img-' + i" data-type="Images" data-folder="projects"
                                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-2.5 py-1.5 rounded mb-2 whitespace-nowrap">
                                    Chọn ảnh
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input x-model="mat.name_vi" type="text" placeholder="Tên tiếng Việt"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <input x-model="mat.name_en" type="text" placeholder="Name English"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="grid grid-cols-2 gap-2 mt-2">
                                <input x-model="mat.detail_vi" type="text" placeholder="Chi tiết tiếng Việt"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                <input x-model="mat.detail_en" type="text" placeholder="Detail English"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </template>

                    <p x-show="materials.length === 0" class="text-xs text-gray-500">Chưa có vật liệu nào.</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Thư viện ảnh</h2>
                        <button type="button" @click="addImage()" class="text-blue-600 hover:underline text-sm">+ Thêm ảnh</button>
                    </div>

                    <template x-for="(img, i) in gallery" :key="i">
                        <div class="border border-gray-200 rounded p-3 mb-3 bg-gray-50 flex items-start gap-2">
                            <div class="flex-1">
                                <div class="flex gap-2">
                                    <input :id="'x-gallery-img-' + i" x-model="img.image" type="text" placeholder="Đường dẫn ảnh"
                                        class="flex-1 border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <button type="button" :data-finder="'x-gallery-img-' + i" data-type="Images" data-folder="projects"
                                        class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-2.5 py-1.5 rounded whitespace-nowrap">
                                        Chọn ảnh
                                    </button>
                                </div>
                                <input x-model="img.alt" type="text" placeholder="Alt text"
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm mt-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="w-20">
                                <label class="block text-xs font-semibold mb-1">Thứ tự</label>
                                <input x-model="img.sort_order" type="number"
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <button type="button" @click="gallery.splice(i, 1)" class="text-red-500 mt-6">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </template>

                    <p x-show="gallery.length === 0" class="text-xs text-gray-500">Chưa có ảnh nào.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 mb-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                {{ $project ? 'Lưu thay đổi' : 'Tạo dự án' }}
            </button>
            <a href="{{ route('admin.projects.index') }}" class="text-gray-600 hover:underline">Hủy</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function projectForm(initial = {}) {
        return {
            lang: 'vi',
            materials: initial.materials ?? [],
            gallery: initial.gallery ?? [],

            addMaterial() {
                this.materials.push({ product_id: '', image: '', sort_order: this.materials.length, name_vi: '', detail_vi: '', name_en: '', detail_en: '' });
            },
            addImage() {
                this.gallery.push({ image: '', alt: '', sort_order: this.gallery.length });
            },
            mergeDynamic() {
                this.materials.forEach((mat, i) => {
                    for (const key of ['product_id', 'image', 'sort_order', 'name_vi', 'detail_vi', 'name_en', 'detail_en']) {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = `materials[${i}][${key}]`;
                        el.value = mat[key];
                        this.$el.appendChild(el);
                    }
                });
                this.gallery.forEach((img, i) => {
                    for (const key of ['image', 'alt', 'sort_order']) {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = `gallery[${i}][${key}]`;
                        el.value = img[key];
                        this.$el.appendChild(el);
                    }
                });
            }
        }
    }
</script>
@endpush
