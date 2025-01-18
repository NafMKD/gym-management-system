<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class AuditTrail extends Model
{
    use SoftDeletes, HelperTrait;

    protected $fillable = [
        'table_name',
        'record_id',
        'user_id',
        'action',
        'changed_data',
    ];

    protected $casts = [
        'changed_data' => 'array',
    ];

    /**
     * Get the user that performed the action
     * 
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
