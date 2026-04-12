<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainerSessionFeedback extends Model
{
    use SoftDeletes;

    /** @var string Laravel's inflector does not pluralize "Feedback" → use explicit table name. */
    protected $table = 'trainer_session_feedbacks';

    protected $fillable = [
        'class_booking_id',
        'rating',
        'comment',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'integer',
        ];
    }

    public function classBooking(): BelongsTo
    {
        return $this->belongsTo(ClassBooking::class, 'class_booking_id');
    }
}
