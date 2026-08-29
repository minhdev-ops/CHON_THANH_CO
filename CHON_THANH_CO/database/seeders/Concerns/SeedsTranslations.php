<?php

namespace Database\Seeders\Concerns;

trait SeedsTranslations
{
    /**
     * Build a createMany() payload from a locale => fields map.
     *
     * @param  array<string, array<string, mixed>>  $data
     */
    protected function translations(array $data): array
    {
        return collect($data)
            ->map(fn (array $fields, string $locale) => ['locale' => $locale] + $fields)
            ->values()
            ->all();
    }
}
