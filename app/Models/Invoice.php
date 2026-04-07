<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes, HelperTrait;

    protected $fillable = [
        'membership_id',
        'user_id',
        'invoice_number',
        'amount',
        'status',
        'issued_date',
        'due_date',
        'invoice_source',
    ];

    /**
     * Get the Membership that owns this Invoice
     *
     * @return BelongsTo
     */
    public function membership(): BelongsTo
    {
        return $this->belongsTo(Membership::class, 'membership_id');
    }

    /**
     * Linked member for merchandise invoices; null means walk-in (no member on file).
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Line items for merchandise invoices.
     */
    public function merchandiseSaleLines(): HasMany
    {
        return $this->hasMany(MerchandiseSaleLine::class, 'invoice_id');
    }

    /**
     * Get the payments associated with this Invoice
     * 
     * @return HasMany
     */
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'invoice_id');
    }
}
