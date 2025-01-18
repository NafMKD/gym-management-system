<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes, HelperTrait;


    protected $fillable = [
        'invoice_id',
        'membership_id',
        'amount',
        'payment_date',
        'payment_method',
        'payment_bank',
        'bank_transaction_number',
        'status',
    ];

    /**
     * Get the invoice that owns the Payment
     * 
     * @return BelongsTo
     */
    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the membership that owns the Payment
     * 
     * @return BelongsTo
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class);
    }
}
