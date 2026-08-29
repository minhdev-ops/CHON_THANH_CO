<?php

namespace App\Support\Concerns;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

trait AddsLocaleCheck
{
    protected function addLocaleCheck(string $table): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_locale_check` CHECK (`locale` IN ('vi', 'en'))"
        );
    }
}
