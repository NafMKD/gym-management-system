<?php

namespace App\Repositories;

use App\Models\GymClass;
use Illuminate\Support\Facades\DB;

class GymClassRepository extends BaseRepository
{
    public function store(array $attributes): mixed
    {
        return DB::transaction(function () use ($attributes) {
            $data = [
                'name' => $attributes['name'] ?? null,
                'description' => $attributes['description'] ?? null,
                'capacity' => $attributes['capacity'] ?? 10,
                'duration_minutes' => $attributes['duration_minutes'] ?? 60,
                'is_active' => array_key_exists('is_active', $attributes) ? (bool) $attributes['is_active'] : true,
            ];

            if (! isset($data['name'])) {
                throw new \Exception(__('Name is required.'));
            }

            return GymClass::create($data);
        });
    }

    public function update(mixed $model, array $attributes): mixed
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var GymClass $model */
            $updates = array_filter([
                'name' => $attributes['name'] ?? null,
                'description' => $attributes['description'] ?? null,
                'capacity' => $attributes['capacity'] ?? null,
                'duration_minutes' => $attributes['duration_minutes'] ?? null,
                'is_active' => array_key_exists('is_active', $attributes) ? (bool) $attributes['is_active'] : null,
            ], fn ($v) => $v !== null);

            if (empty($updates)) {
                throw new \App\Exceptions\NoUpdateNeededException();
            }

            $model->update($updates);

            return $model->fresh();
        });
    }

    /**
     * Soft-delete class and its schedules (and their bookings).
     */
    public function destroy(mixed $model): bool
    {
        try {
            return DB::transaction(function () use ($model) {
                /** @var GymClass $model */
                $model->load('schedules.bookings');
                foreach ($model->schedules as $schedule) {
                    $schedule->bookings()->each(fn ($b) => $b->delete());
                    $schedule->delete();
                }
                $model->delete();

                return true;
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
