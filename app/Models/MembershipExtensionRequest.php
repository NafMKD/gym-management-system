<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MembershipExtensionRequest extends Model
{
    use SoftDeletes, HelperTrait;

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'membership_id',
        'requested_by',
        'reason',
        'requested_days',
        'status',
        'approved_by',
        'rejection_reason',
    ];

    /**
     * @var array<string, string>
     */
    protected $casts = [
        'requested_days' => 'integer',
    ];

    /**
     * @return BelongsTo
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id');
    }

    /**
     * @return BelongsTo
     */
    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    /**
     * @return BelongsTo
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}
