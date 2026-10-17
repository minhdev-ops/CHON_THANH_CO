@extends('admin.layouts.app')

@section('title', 'Sản phẩm')

@section('content')
    @php
        $richTextHint = 'Dòng bắt đầu bằng "## " tạo tiêu đề, "### " tạo tiêu đề nhỏ hơn, "- " tạo gạch đầu dòng, "1. " tạo danh sách đánh số, "**chữ**" in đậm, dòng trống tách đoạn.';

        $productSpecs = $product?->specs ?? collect();
        $productImages = $product?->images ?? collect();

        if (old('specs') !== null) {
            $productSpecsData = old('specs');
        } else {
            $productSpecsData = [];
            foreach ($productSpecs as $s) {
                $productSpecsData[] = [
                    'value' => $s->value,
                    'icon' => $s->icon,
                    'sort_order' => $s->sort_order,
                    'label' => $s?->label ?? '',
                ];
            }
        }

        if (old('images') !== null) {
            $productImagesData = old('images');
        } else {
            $productImagesData = [];
            foreach ($productImages as $img) {
                $productImagesData[] = [
                    'image' => $img->image,
                    'alt' => $img->alt,
                    'sort_order' => $img->sort_order,
                ];
            }
        }
    $productStrengthGrades = $product?->strengthGrades ?? collect();

        if (old('strength_grades') !== null) {
            $productStrengthGradesData = old('strength_grades');
        } else {
            $productStrengthGradesData = [];
            foreach ($productStrengthGrades as $g) {
                $productStrengthGradesData[] = [
                    'grade' => $g->grade,
                    'unit' => $g->unit,
                    'note' => $g->note,
                    'sort_order' => $g->sort_order,
                ];
            }
        }
    @endphp

    <form method="POST" action="{{ $product ? route('admin.products.update', $product) : route('admin.products.store') }}"
        x-data="productForm({ specs: @json($productSpecsData), images: @json($productImagesData), strengthGrades: @json($productStrengthGradesData) })"
        @submit="mergeDynamic()">
        @csrf
        @if ($product)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mb-6">
            <div class="xl:col-span-2 space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Thông tin chung</h2>

                    <div class="grid grid-cols-2 gap-4">
                        <x-admin.partials.input name="code" label="Mã sản phẩm" :value="$product?->code" :hint="'VD: ART 30, GET 200'" required />
                        <x-admin.partials.input name="slug" label="Slug" :value="$product?->slug" :hint="'Để trống để tự tạo từ tên tiếng Việt.'" />
                    </div>

                    <x-admin.partials.image-picker name="image" label="Đường dẫn ảnh chính" :value="$product?->image" :hint="'Bấm nút Chọn ảnh để upload hoặc chọn từ thư viện.'" folder="products" required />

                    @if ($product?->image)
                        <img src="{{ $product->image }}" alt="" class="h-24 w-auto object-contain mb-4 border border-gray-200 rounded">
                    @endif

                    <div class="grid grid-cols-2 gap-4">
                        <x-admin.partials.input name="strength_min" label="Cường độ tối thiểu (kN/m)" type="number" :value="$product?->strength_min" step="0.01" />
                        <x-admin.partials.input name="strength_max" label="Cường độ tối đa (kN/m)" type="number" :value="$product?->strength_max" step="0.01" />
                    </div>

                    <div class="grid grid-cols-3 gap-4">
                        <x-admin.partials.input name="sort_order" label="Thứ tự" type="number" :value="$product?->sort_order" min="0" />
                        <div class="pt-7">
                            <x-admin.partials.checkbox name="is_featured" label="Nổi bật" :checked="$product?->is_featured ?? false" />
                        </div>
                        <div class="pt-7">
                            <x-admin.partials.checkbox name="is_active" label="Hiển thị" :checked="$product?->is_active ?? true" />
                        </div>
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
                            ['name' => 'name', 'label' => 'Tên sản phẩm', 'required' => true, 'max' => 200],
                            ['name' => 'description', 'label' => 'Mô tả', 'type' => 'textarea', 'required' => true, 'rows' => 8, 'hint' => 'Đoạn đầu tiên hiện làm câu dẫn ở đầu trang chi tiết. '.$richTextHint],
                            ['name' => 'strength_label', 'label' => 'Nhãn cường độ', 'max' => 50, 'hint' => 'VD: Cường độ 30 kN/m'],
                            ['name' => 'meta_title', 'label' => 'Meta title (SEO)', 'max' => 200],
                            ['name' => 'meta_description', 'label' => 'Meta description (SEO)', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                        ]"
                        :values="$product?->toArray() ?? []"
                    />

                    <x-admin.partials.translation-section
                        locale="en"
                        label="English"
                        :fields="[
                            ['name' => 'name', 'label' => 'Product name', 'max' => 200],
                            ['name' => 'description', 'label' => 'Description', 'type' => 'textarea', 'rows' => 8],
                            ['name' => 'strength_label', 'label' => 'Strength label', 'max' => 50],
                            ['name' => 'meta_title', 'label' => 'Meta title', 'max' => 200],
                            ['name' => 'meta_description', 'label' => 'Meta description', 'type' => 'textarea', 'rows' => 2, 'max' => 300],
                        ]"
                        :values="$product?->toArray() ?? []"
                    />
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-1">Nội dung hiển thị</h2>
                    <p class="text-xs text-gray-500 mb-4">
                        Hai tab riêng trên trang chi tiết: <strong>Chức năng</strong> và phần
                        <strong>Ứng dụng chi tiết</strong> nằm dưới danh sách ứng dụng đã chọn.
                    </p>

                    <x-admin.partials.input
                        name="functions"
                        label="Chức năng"
                        type="textarea"
                        :value="$product?->functions"
                        :rows="8"
                        :hint="$richTextHint"
                    />

                    <x-admin.partials.input
                        name="applications_text"
                        label="Ứng dụng (chi tiết)"
                        type="textarea"
                        :value="$product?->applications_text"
                        :rows="6"
                        :hint="'Hiển thị dưới danh sách ứng dụng đã chọn ở trên. Nên viết về bối cảnh sử dụng cụ thể, tránh lặp lại tên ứng dụng. '.$richTextHint"
                    />
                </div>
            </div>

            <div class="space-y-6">
                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <h2 class="font-semibold mb-4">Danh mục & Ứng dụng</h2>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1.5">Thuộc dòng sản phẩm</label>
                        <select name="parent_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Sản phẩm độc lập --</option>
                            @foreach ($parentCandidates as $candidate)
                                <option value="{{ $candidate->id }}" @selected(old('parent_id', $product?->parent_id) == $candidate->id)>
                                    {{ $candidate?->name }} ({{ $candidate?->code }})
                                </option>
                            @endforeach
                        </select>
                        <p class="text-xs text-gray-500 mt-1.5">
                            Chọn dòng để gom các mã cùng loại (ví dụ các độ dày màng HDPE).
                            Trang chi tiết sẽ hiện bảng chọn loại cho người xem.
                            Danh sách này không chứa chính sản phẩm đang sửa và các biến thể của nó.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-semibold mb-1.5">Danh mục <span class="text-red-500">*</span></label>
                        <select name="category_id" class="w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Chọn danh mục --</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $product?->category_id) == $category->id)>{{ $category?->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-semibold mb-1.5">Ứng dụng</label>
                        <div class="space-y-2 max-h-56 overflow-y-auto border border-gray-200 rounded p-3">
                            @foreach ($applications as $application)
                                <label class="flex items-center gap-2 text-sm cursor-pointer">
                                    <input type="checkbox" name="applications[]" value="{{ $application->id }}"
                                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                                        @checked(in_array($application->id, old('applications', $product?->applications?->pluck('id')->all() ?? [])))>
                                    {{ $application?->name }}
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h2 class="font-semibold">Các cấp cường độ</h2>
                            <p class="text-xs text-gray-500 mt-0.5">
                                Dùng cho dòng có nhiều cấp nằm trong cùng một sản phẩm (ART, GET...).
                                Trang chi tiết hiện thành bảng so sánh cho khách chọn.
                            </p>
                        </div>
                        <button type="button" @click="addStrengthGrade()" class="text-blue-600 hover:underline text-sm shrink-0">+ Thêm cấp</button>
                    </div>

                    <template x-for="(grade, i) in strengthGrades" :key="i">
                        <div class="flex items-start gap-2 mb-3">
                            <div class="flex-1">
                                <label class="block text-xs font-semibold mb-1">Cấp (VD: 12, 25, 100)</label>
                                <input x-model="grade.grade" type="text" placeholder="VD: 12"
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-semibold mb-1">Đơn vị</label>
                                <input x-model="grade.unit" type="text" placeholder="kN/m"
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="w-16">
                                <label class="block text-xs font-semibold mb-1">Thứ tự</label>
                                <input x-model="grade.sort_order" type="number"
                                    class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <button type="button" @click="strengthGrades.splice(i, 1)" class="text-red-500 mt-6">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </template>

                    <p x-show="strengthGrades.length === 0" class="text-xs text-gray-500">
                        Chưa có cấp cường độ nào.
                    </p>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Thông số kỹ thuật</h2>
                        <button type="button" @click="addSpec()" class="text-blue-600 hover:underline text-sm">+ Thêm dòng</button>
                    </div>
                    <p class="text-xs text-gray-500 mb-4 -mt-2">
                        Nhãn nên ngắn gọn. Trang chi tiết hiển thị thông số thành lưới 2 cột, để trống giá trị thì dòng đó bị bỏ qua.
                    </p>

                    <template x-for="(spec, i) in specs" :key="i">
                        <div class="border border-gray-200 rounded p-3 mb-3 bg-gray-50">
                            <div class="flex items-start gap-2 mb-2">
                                <div class="flex-1">
                                    <label class="block text-xs font-semibold mb-1">Giá trị (VD: 30 kN/m)</label>
                                    <input x-model="spec.value" type="text" placeholder="VD: 30 kN/m"
                                        class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                </div>
                                <div class="w-32">
                                    <label class="block text-xs font-semibold mb-1">Icon</label>
                                    <input x-model="spec.icon" type="text" placeholder="bolt"
                                        class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                </div>
                                <div class="w-16">
                                    <label class="block text-xs font-semibold mb-1">Thứ tự</label>
                                    <input x-model="spec.sort_order" type="number"
                                        class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                </div>
                                <button type="button" @click="specs.splice(i, 1)" class="text-red-500 mt-6">
                                    <span class="material-symbols-outlined text-[18px]">delete</span>
                                </button>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <input x-model="spec.label" type="text" placeholder="Nhãn (VD: Cường độ kéo)"
                                    class="border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>
                    </template>

                    <p x-show="specs.length === 0" class="text-xs text-gray-500">Chưa có thông số nào.</p>
                </div>

                <div class="bg-white border border-gray-200 rounded-lg p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="font-semibold">Ảnh phụ</h2>
                        <button type="button" @click="addImage()" class="text-blue-600 hover:underline text-sm">+ Thêm ảnh</button>
                    </div>

                    <template x-for="(img, i) in images" :key="i">
                        <div class="border border-gray-200 rounded p-3 mb-3 bg-gray-50 flex items-start gap-2">
                            <div class="flex-1">
                                <div class="flex gap-2">
                                    <input :id="'x-img-' + i" x-model="img.image" type="text" placeholder="Đường dẫn ảnh (VD: /images/products/art/art-30-2.jpg)"
                                        class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <button type="button" :data-finder="'x-img-' + i" data-type="Images" data-folder="products"
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
                            <button type="button" @click="images.splice(i, 1)" class="text-red-500 mt-6">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </template>

                    <p x-show="images.length === 0" class="text-xs text-gray-500">Chưa có ảnh phụ.</p>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 mb-8">
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-6 py-2.5 rounded">
                {{ $product ? 'Lưu thay đổi' : 'Tạo sản phẩm' }}
            </button>
            <a href="{{ route('admin.products.index') }}" class="text-gray-600 hover:underline">Hủy</a>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    function productForm(initial = {}) {
        return {
            lang: 'vi',
            specs: initial.specs ?? [],
            images: initial.images ?? [],
            strengthGrades: initial.strengthGrades ?? [],

            addSpec() {
                this.specs.push({ value: '', icon: '', sort_order: this.specs.length, label: '' });
            },

            addStrengthGrade() {
                this.strengthGrades.push({
                    grade: '',
                    unit: '',
                    note: '',
                    sort_order: this.strengthGrades.length,
                });
            },
            addImage() {
                this.images.push({ image: '', alt: '', sort_order: this.images.length });
            },
            mergeDynamic() {
                this.specs.forEach((spec, i) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = `specs[${i}][value]`;
                    input.value = spec.value;
                    this.$el.appendChild(input);

                    for (const key of ['icon', 'sort_order', 'label']) {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = `specs[${i}][${key}]`;
                        el.value = spec[key];
                        this.$el.appendChild(el);
                    }
                });
                this.images.forEach((img, i) => {
                    for (const key of ['image', 'alt', 'sort_order']) {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = `images[${i}][${key}]`;
                        el.value = img[key];
                        this.$el.appendChild(el);
                    }
                });
                this.strengthGrades.forEach((grade, i) => {
                    for (const key of ['grade', 'unit', 'note', 'sort_order']) {
                        const el = document.createElement('input');
                        el.type = 'hidden';
                        el.name = `strength_grades[${i}][${key}]`;
                        el.value = grade[key];
                        this.$el.appendChild(el);
                    }
                });
            }
        }
    }
</script>
@endpush
