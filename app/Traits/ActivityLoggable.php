<?php

namespace App\Traits;

use App\Models\ActivityLog;

trait ActivityLoggable
{
    public static function bootActivityLoggable(): void
    {
        static::created(function ($model): void {
            $model->recordActivityLog($model->getActivityLogType() . '.created');
        });

        static::updated(function ($model): void {
            $changes = collect($model->getChanges())
                ->except(['updated_at'])
                ->all();

            if ($changes === []) {
                return;
            }

            $model->recordActivityLog($model->getActivityLogType() . '.updated', [
                'changes' => $changes,
            ]);
        });

        static::deleted(function ($model): void {
            $model->recordActivityLog($model->getActivityLogType() . '.deleted');
        });
    }

    protected function recordActivityLog(string $action, array $extraData = []): void
    {
        ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $this->getMorphClass(),
            'subject_id' => $this->getKey(),
            'data' => array_merge([
                'label' => $this->getActivityLogLabel(),
                'attributes' => $this->activityLogAttributes(),
            ], $extraData),
        ]);
    }

    protected function getActivityLogType(): string
    {
        return str(class_basename($this))->snake()->toString();
    }

    protected function getActivityLogLabel(): ?string
    {
        foreach (['nombre', 'name', 'patente', 'email', 'numero_factura', 'numero_identificacion'] as $attribute) {
            if (isset($this->{$attribute})) {
                return (string) $this->{$attribute};
            }
        }

        return $this->getKey() ? '#' . $this->getKey() : null;
    }

    protected function activityLogAttributes(): array
    {
        return collect($this->getAttributes())
            ->except(['password', 'remember_token'])
            ->all();
    }
}
