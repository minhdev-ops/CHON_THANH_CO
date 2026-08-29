<?php

namespace App\Observers;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

class AuditObserver
{
    public function created(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $this->log('created', $model, [
            'attributes' => $this->sanitize($model->getAttributes()),
        ]);
    }

    public function updated(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $after = $model->getChanges();
        unset($after['updated_at']);

        if (empty($after)) {
            return;
        }

        $before = [];
        foreach (array_keys($after) as $key) {
            $before[$key] = $model->getOriginal($key);
        }

        $this->log('updated', $model, [
            'before' => $this->sanitize($before),
            'after' => $this->sanitize($after),
        ]);
    }

    public function deleted(Model $model): void
    {
        if ($this->shouldSkip($model)) {
            return;
        }

        $this->log('deleted', $model, [
            'attributes' => $this->sanitize($model->getAttributes()),
        ]);
    }

    protected function shouldSkip(Model $model): bool
    {
        return $model instanceof AuditLog || ! session()->has('admin_authenticated');
    }

    protected function log(string $action, Model $model, array $changes): void
    {
        AuditLog::create([
            'actor' => session('admin_name', 'Admin'),
            'action' => $action,
            'model_type' => $model::class,
            'model_id' => $model->getKey(),
            'changes' => $changes,
        ]);
    }

    protected function sanitize(array $attributes): array
    {
        return collect($attributes)
            ->reject(fn ($value, $key) => in_array($key, ['created_at', 'updated_at', 'deleted_at', 'remember_token'], true))
            ->map(fn ($value) => is_array($value) || is_object($value) ? json_encode($value) : $value)
            ->all();
    }
}
