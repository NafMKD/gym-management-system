<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GymClass extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'capacity',
        'duration_minutes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'capacity' => 'integer',
            'duration_minutes' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Scheduled sessions for this class type.
     */
    public function schedules(): HasMany
    {
        return $this->hasMany(ClassSchedule::class, 'gym_class_id');
    }
}
