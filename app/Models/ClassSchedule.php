<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ClassSchedule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'gym_class_id',
        'trainer_id',
        'starts_at',
        'ends_at',
        'capacity_override',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'capacity_override' => 'integer',
        ];
    }

    public function gymClass(): BelongsTo
    {
        return $this->belongsTo(GymClass::class, 'gym_class_id');
    }

    public function trainer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'trainer_id');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(ClassBooking::class, 'class_schedule_id');
    }

    /**
     * Max participants for this session (class default unless overridden).
     */
    public function effectiveCapacity(): int
    {
        if ($this->capacity_override !== null) {
            return max(1, (int) $this->capacity_override);
        }

        return max(1, (int) $this->gymClass?->capacity ?? 1);
    }

    /**
     * Bookings that count toward capacity.
     */
    public function activeBookingsCount(): int
    {
        return $this->bookings()
            ->whereIn('status', ['pending', 'confirmed', 'attended'])
            ->count();
    }
}
