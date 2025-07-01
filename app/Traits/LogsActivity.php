<?php

namespace App\Traits;

use App\Models\ActionLog;

trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::created(function ($model) {
            $model->logActivity('created');
        });

        static::updated(function ($model) {
            $model->logActivity('updated');
        });

        static::deleted(function ($model) {
            $model->logActivity('deleted');
        });
    }

    public function logActivity(string $action): void
    {
        $userId = auth()->check() ? auth()->id() : null;

        ActionLog::create([
            'user_id' => $userId,
            'action' => $action,
            'model_type' => get_class($this),
            'model_id' => $this->id,
            'old_values' => $action === 'updated' ? $this->getOriginal() : null,
            'new_values' => $action !== 'deleted' ? $this->getAttributes() : null,
            'description' => "Model ". get_class($this) ." was $action",
        ]);
    }

}
