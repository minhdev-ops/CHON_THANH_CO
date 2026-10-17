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

            $name = trim((string) ($row['name'] ?? $row['name_vi'] ?? ''));

            if ($name === '') {
                $this->errors[] = "Dòng {$this->excelRow} ({$code}): thiếu tên sản phẩm (name).";

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

            $slug = $this->resolveSlug($row['slug'] ?? null, $name, $existing);

            $data = [
                'category_id' => $category->id,
                'code' => $code,
                'slug' => $slug,
                'name' => $name,
                'description' => trim((string) ($row['description'] ?? $row['description_vi'] ?? '')),
                'strength_label' => trim((string) ($row['strength_label'] ?? $row['strength_label_vi'] ?? '')),
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
            ?? $categories->first(fn ($c) => strtolower(trim((string) $c->name)) === $raw);
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
                ?? $applications->first(fn ($a) => strtolower(trim((string) $a->name)) === $piece);

            if ($app) {
                $ids[] = $app->id;
            } else {
                $this->errors[] = "Dòng {$this->excelRow} ({$code}): ứng dụng '{$piece}' không tồn tại, đã bỏ qua.";
            }
        }

        return $ids;
    }

    private function resolveSlug(mixed $value, string $name, ?Product $existing): string
    {
        $slug = Str::slug(trim((string) $value));

        if ($slug === '') {
            $slug = Str::slug($name);
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