<?php

namespace App\Imports;

use App\Models\Application;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ProductsImport implements SkipsEmptyRows, ToCollection, WithHeadingRow
{
    use Importable;

    public int $imported = 0;

    public int $created = 0;

    public int $updated = 0;

    public array $errors = [];

    private int $excelRow = 1;

    public function collection(Collection $rows): void
    {
        $categories = Category::all();
        $applications = Application::all();

        foreach ($rows as $row) {
            $this->excelRow++;

            $code = trim((string) ($row['code'] ?? ''));

            if ($code === '') {
                $this->errors[] = "Dòng {$this->excelRow}: thiếu mã sản phẩm (code).";

                continue;
            }

            $nameVi = trim((string) ($row['name_vi'] ?? ''));

            if ($nameVi === '') {
                $this->errors[] = "Dòng {$this->excelRow} ({$code}): thiếu tên sản phẩm tiếng Việt (name_vi).";

                continue;
            }

            $category = $this->resolveCategory($row['category'] ?? null, $categories);

            if (! $category) {
                $this->errors[] = "Dòng {$this->excelRow} ({$code}): danh mục '{$row['category']}' không tồn tại.";

                continue;
            }

            $existing = Product::withTrashed()->where('code', $code)->first();

            if ($existing?->trashed()) {
                $existing->restore();
            }

            $wasExisting = (bool) $existing;

            $slug = $this->resolveSlug($row['slug'] ?? null, $nameVi, $existing);

            $data = [
                'category_id' => $category->id,
                'code' => $code,
                'slug' => $slug,
            ];

            if ($this->hasValue($row['image'] ?? null) || ! $wasExisting) {
                $data['image'] = trim((string) ($row['image'] ?? ''));
            }

            if ($this->hasValue($row['strength_min'] ?? null) || ! $wasExisting) {
                $data['strength_min'] = $this->nullableNumber($row['strength_min'] ?? null);
            }

            if ($this->hasValue($row['strength_max'] ?? null) || ! $wasExisting) {
                $data['strength_max'] = $this->nullableNumber($row['strength_max'] ?? null);
            }

            if ($this->hasValue($row['is_featured'] ?? null) || ! $wasExisting) {
                $data['is_featured'] = $this->toBool($row['is_featured'] ?? null, false);
            }

            if ($this->hasValue($row['is_active'] ?? null) || ! $wasExisting) {
                $data['is_active'] = $this->toBool($row['is_active'] ?? null, true);
            }

            if ($this->hasValue($row['sort_order'] ?? null) || ! $wasExisting) {
                $data['sort_order'] = (int) ($row['sort_order'] ?? 0);
            }

            $product = $existing ? $existing->update($data) ? $existing : $existing : Product::create($data);

            if (! $existing) {
                $this->created++;
            } else {
                $this->updated++;
            }
            $this->imported++;

            $rawApps = trim((string) ($row['applications'] ?? ''));

            if (! $wasExisting || $rawApps !== '') {
                $product->applications()->sync($this->resolveApplications($rawApps, $applications, $code));
            }

            $this->syncTranslation($product, 'vi', $nameVi, $row['description_vi'] ?? null, $row['strength_label_vi'] ?? null);
            $this->syncTranslation($product, 'en', trim((string) ($row['name_en'] ?? '')), $row['description_en'] ?? null, $row['strength_label_en'] ?? null);
        }
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function resolveCategory(mixed $value, Collection $categories): ?Category
    {
        $raw = strtolower(trim((string) $value));

        if ($raw === '') {
            return null;
        }

        return $categories->first(fn ($c) => strtolower($c->slug) === $raw)
            ?? $categories->first(fn ($c) => strtolower(trim((string) $c->translation('vi')?->name)) === $raw);
    }

    private function resolveApplications(string $raw, Collection $applications, string $code): array
    {
        if ($raw === '') {
            return [];
        }

        $ids = [];

        foreach (preg_split('/[,;\n]/', $raw) as $piece) {
            $piece = strtolower(trim($piece));

            if ($piece === '') {
                continue;
            }

            $app = $applications->first(fn ($a) => strtolower($a->slug) === $piece)
                ?? $applications->first(fn ($a) => strtolower(trim((string) $a->translation('vi')?->name)) === $piece);

            if ($app) {
                $ids[] = $app->id;
            } else {
                $this->errors[] = "Dòng {$this->excelRow} ({$code}): ứng dụng '{$piece}' không tồn tại, đã bỏ qua.";
            }
        }

        return $ids;
    }

    private function resolveSlug(mixed $value, string $nameVi, ?Product $existing): string
    {
        $slug = Str::slug(trim((string) $value));

        if ($slug === '') {
            $slug = Str::slug($nameVi);
        }

        $slug = $slug ?: 'product-'.Str::random(6);
        $base = $slug;
        $i = 1;

        $query = Product::withTrashed()->where('slug', $slug);

        if ($existing) {
            $query->where('id', '!=', $existing->id);
        }

        while ($query->exists()) {
            $slug = $base.'-'.(++$i);
            $query = Product::withTrashed()->where('slug', $slug);

            if ($existing) {
                $query->where('id', '!=', $existing->id);
            }
        }

        return $slug;
    }

    private function syncTranslation(Product $product, string $locale, string $name, mixed $description, mixed $strengthLabel): void
    {
        $description = trim((string) $description);
        $strengthLabel = trim((string) $strengthLabel);

        if ($name === '' && $description === '' && $strengthLabel === '') {
            if ($locale === 'en') {
                return;
            }

            $product->translations()->where('locale', $locale)->delete();

            return;
        }

        $translation = $product->translations()->where('locale', $locale)->first();

        $data = [];

        if ($name !== '') {
            $data['name'] = $name;
        }

        if ($description !== '') {
            $data['description'] = $description;
        }

        if ($strengthLabel !== '') {
            $data['strength_label'] = $strengthLabel;
        }

        if ($translation) {
            $data['name'] = $data['name'] ?? $translation->name;
            $data['description'] = $data['description'] ?? $translation->description;
            $data['strength_label'] = $data['strength_label'] ?? $translation->strength_label;
            $translation->update($data);
        } else {
            $data['name'] = $data['name'] ?? ($locale === 'vi' ? 'Chưa đặt tên' : $name);
            $data['description'] = $data['description'] ?? '';
            $data['strength_label'] = $data['strength_label'] ?? '';
            $product->translations()->create(array_merge(['locale' => $locale], $data));
        }
    }

    private function hasValue(mixed $value): bool
    {
        return trim((string) $value) !== '';
    }

    private function nullableNumber(mixed $value): ?float
    {
        $value = trim((string) $value);

        return $value === '' ? null : (float) $value;
    }

    private function toBool(mixed $value, bool $default): bool
    {
        $value = strtolower(trim((string) $value));

        return match ($value) {
            '1', 'true', 'yes', 'y', 'có', 'đúng', 'on' => true,
            '0', 'false', 'no', 'n', 'không', 'off' => false,
            default => $default,
        };
    }
}
