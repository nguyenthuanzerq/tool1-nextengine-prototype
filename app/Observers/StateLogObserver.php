<?php

namespace App\Observers;

use App\Models\StateLog;
use Illuminate\Database\Eloquent\Model;

class StateLogObserver
{
    /**
     * Handle the Model "created" event.
     */
    public function created(Model $model): void
    {
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            return;
        }

        StateLog::create([
            'model_type' => class_basename($model),
            'model_id' => $model->getKey(),
            'action' => 'created',
            'old_state' => null,
            'new_state' => $model->getAttributes(),
            'user_id' => auth()->id(),
            'triggered_by_url' => request()->url(),
            'triggered_by_route' => request()->route() ? request()->route()->getName() : null,
        ]);
    }

    /**
     * Handle the Model "updated" event.
     */
    public function updated(Model $model): void
    {
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            return;
        }

        // Only log if something actually changed
        if (empty($model->getChanges())) {
            return;
        }

        // getChanges() contains the new values, and getOriginal() contains the old.
        // We only want to log the fields that were modified.
        $changes = $model->getChanges();
        $oldState = [];
        $newState = [];

        foreach ($changes as $key => $value) {
            // Ignore updated_at column changes if it's the only thing changing
            if ($key === $model->getUpdatedAtColumn() && count($changes) === 1) {
                return;
            }
            $oldState[$key] = $model->getOriginal($key);
            $newState[$key] = $value;
        }

        StateLog::create([
            'model_type' => class_basename($model),
            'model_id' => $model->getKey(),
            'action' => 'updated',
            'old_state' => $oldState,
            'new_state' => $newState,
            'user_id' => auth()->id(),
            'triggered_by_url' => request()->url(),
            'triggered_by_route' => request()->route() ? request()->route()->getName() : null,
        ]);
    }

    /**
     * Handle the Model "deleted" event.
     */
    public function deleted(Model $model): void
    {
        if (!config('app.debug_logs', env('ENABLE_DEBUG_LOGS', false))) {
            return;
        }

        StateLog::create([
            'model_type' => class_basename($model),
            'model_id' => $model->getKey(),
            'action' => 'deleted',
            'old_state' => $model->getAttributes(),
            'new_state' => null,
            'user_id' => auth()->id(),
            'triggered_by_url' => request()->url(),
            'triggered_by_route' => request()->route() ? request()->route()->getName() : null,
        ]);
    }
}
