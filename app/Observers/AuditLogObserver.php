<?php

namespace App\Observers;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditLogObserver
{
    public function created(Model $model): void
    {
        $this->log('created', $model);
    }

    public function updated(Model $model): void
    {
        $this->log('updated', $model);
    }

    public function deleted(Model $model): void
    {
        $this->log('deleted', $model);
    }

    private function log(string $action, Model $model): void
    {
        if ($model instanceof AuditLog) return;

        AuditLog::create([
            'user_id'    => Auth::id() ?? ($model instanceof User ? $model->getKey() : null), // ✅ if registering, use the new user's own id
            'action'     => $action,
            'table_name' => $model->getTable(),
            'record'     => $model->toArray(),
        ]);
    }
}
