<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;

trait Localizable
{
    public function translation(?string $locale = null): ?Model
    {
        $locale ??= app()->getLocale();

        return $this->translations
            ->first(fn ($translation) => $translation->locale === $locale)
            ?? $this->translations->first(fn ($translation) => $translation->locale === 'vi');
    }
}
