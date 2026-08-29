<?php

namespace App\Http\Controllers\Admin\Concerns;

trait SyncsTranslations
{
    /**
     * Replace all translations of a localizable model from request data.
     *
     * @param  array<string, array<string, mixed>>  $translations  keyed by locale
     * @param  array<int, string>  $fields
     */
    protected function syncTranslations($model, array $translations, array $fields): void
    {
        $model->translations()->delete();

        foreach (['vi', 'en'] as $locale) {
            $data = $translations[$locale] ?? [];
            $row = [];
            $hasValue = false;

            foreach ($fields as $field) {
                $value = $data[$field] ?? null;
                // Middleware ConvertEmptyStringsToNull chuyển '' thành null;
                // quy về '' để không vi phạm ràng buộc NOT NULL của cột text.
                $row[$field] = $value ?? '';
                if ($value !== null && $value !== '') {
                    $hasValue = true;
                }
            }

            if ($hasValue) {
                $model->translations()->create(array_merge(['locale' => $locale], $row));
            }
        }
    }
}
