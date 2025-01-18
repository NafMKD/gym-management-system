<?php 

namespace App\Observers;

use App\Models\AuditTrail;
use Illuminate\Support\Facades\Auth;

class AuditTrailObserver
{
    /**
     * Summary of created
     * 
     * @param mixed $model
     * @return void
     */
    public function created($model): void
    {
        AuditTrail::create([
            'table_name' => $model->getTable(),
            'record_id' => $model->id,
            'user_id' => Auth::id() ?? 0,
            'action' => 'insert',
            'changed_data' => json_encode($model->getAttributes()),
        ]);
    }

    /**
     * Summary of updated
     * 
     * @param mixed $model
     * @return void
     */
    public function updated($model): void
    {
        AuditTrail::create([
            'table_name' => $model->getTable(),
            'record_id' => $model->id,
            'user_id' => Auth::id() ?? 0,
            'action' => 'update',
            'changed_data' => json_encode([
                'before' => $model->getOriginal(),
                'after' => $model->getChanges(),
            ]),
        ]);
    }

    /**
     * Summary of deleted
     * 
     * @param mixed $model
     * @return void
     */
    public function deleted($model): void
    {
        AuditTrail::create([
            'table_name' => $model->getTable(),
            'record_id' => $model->id,
            'user_id' => Auth::id() ?? 0,
            'action' => 'delete',
            'changed_data' => json_encode($model->getOriginal()),
        ]);
    }
}