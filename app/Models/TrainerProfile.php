<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainerProfile extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'qualifications',
        'specializations',
        'bio',
        'commission_per_session',
    ];

    protected function casts(): array
    {
        return [
            'commission_per_session' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
