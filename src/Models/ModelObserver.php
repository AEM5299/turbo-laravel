<?php

namespace HotwiredLaravel\TurboLaravel\Models;

use Illuminate\Database\Eloquent\Model;

class ModelObserver
{
    /**
     * Only dispatch the observer's events after all database transactions have committed.
     *
     * @var bool
     */
    public $afterCommit;

    public function __construct()
    {
        $this->afterCommit = config('turbo-laravel.queue');
    }

    /**
     * @param  Model|Broadcasts  $model
     */
    public function saved(Model $model)
    {
        if ($this->shouldBroadcastRefresh($model)) {
            if ($model->wasRecentlyCreated) {
                $model->broadcastRefreshCreated()->later();
            } else {
                $model->broadcastRefresh()->later();
            }
        }

        if ($this->shouldBroadcast($model)) {
            if ($model->wasRecentlyCreated) {
                $model->broadcastInsert()->later();
            } else {
                $model->broadcastReplace()->later();
            }
        }
    }

    /**
     * @param  Model|Broadcasts  $model
     */
    public function deleted(Model $model)
    {
        if ($this->shouldBroadcastRefresh($model)) {
            $model->broadcastRefresh()->later();
        }

        if ($this->shouldBroadcast($model)) {
            $model->broadcastRemove()->later();
        }
    }

    private function shouldBroadcastRefresh(Model $model): bool
    {
        if (property_exists($model, 'broadcastRefreshes')) {
            return true;
        }

        if (property_exists($model, 'broadcastRefreshesTo')) {
            return true;
        }

        return false;
    }

    private function shouldBroadcast(Model $model): bool
    {
        if (property_exists($model, 'broadcasts')) {
            return true;
        }

        if (property_exists($model, 'broadcastsTo')) {
            return true;
        }

        return method_exists($model, 'broadcastsTo');
    }
}
