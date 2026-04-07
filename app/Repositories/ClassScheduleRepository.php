<?php

namespace App\Repositories;

use App\Models\ClassSchedule;
use App\Models\User;
use Carbon\Carbon;
use DateTimeInterface;
use Illuminate\Support\Facades\DB;

class ClassScheduleRepository extends BaseRepository
{
    public function store(array $attributes): mixed
    {
        return DB::transaction(function () use ($attributes) {
            $trainerId = (int) ($attributes['trainer_id'] ?? 0);
            $this->assertTrainerRole($trainerId);

            $start = Carbon::parse($attributes['starts_at']);
            $end = Carbon::parse($attributes['ends_at']);

            if ($end->lte($start)) {
                throw new \Exception(__('End time must be after start time.'));
            }

            $this->assertNoTrainerOverlap($trainerId, $start, $end, null);

            return ClassSchedule::create([
                'gym_class_id' => $attributes['gym_class_id'],
                'trainer_id' => $trainerId,
                'starts_at' => $start,
                'ends_at' => $end,
                'capacity_override' => $attributes['capacity_override'] ?? null,
                'notes' => $attributes['notes'] ?? null,
            ]);
        });
    }

    public function update(mixed $model, array $attributes): mixed
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var ClassSchedule $model */
            $trainerId = isset($attributes['trainer_id']) ? (int) $attributes['trainer_id'] : (int) $model->trainer_id;
            if (isset($attributes['trainer_id'])) {
                $this->assertTrainerRole($trainerId);
            }

            $start = isset($attributes['starts_at']) ? Carbon::parse($attributes['starts_at']) : $model->starts_at;
            $end = isset($attributes['ends_at']) ? Carbon::parse($attributes['ends_at']) : $model->ends_at;

            if ($end->lte($start)) {
                throw new \Exception(__('End time must be after start time.'));
            }

            $this->assertNoTrainerOverlap($trainerId, $start, $end, $model->id);

            $updates = [];
            if (isset($attributes['gym_class_id'])) {
                $updates['gym_class_id'] = $attributes['gym_class_id'];
            }
            if (isset($attributes['trainer_id'])) {
                $updates['trainer_id'] = $trainerId;
            }
            if (isset($attributes['starts_at'])) {
                $updates['starts_at'] = Carbon::parse($attributes['starts_at']);
            }
            if (isset($attributes['ends_at'])) {
                $updates['ends_at'] = Carbon::parse($attributes['ends_at']);
            }
            if (array_key_exists('capacity_override', $attributes)) {
                $updates['capacity_override'] = $attributes['capacity_override'];
            }
            if (array_key_exists('notes', $attributes)) {
                $updates['notes'] = $attributes['notes'];
            }

            if (empty($updates)) {
                throw new \App\Exceptions\NoUpdateNeededException();
            }

            $model->update($updates);

            return $model->fresh();
        });
    }

    public function destroy(mixed $model): bool
    {
        try {
            return DB::transaction(function () use ($model) {
                /** @var ClassSchedule $model */
                $model->bookings()->each(fn ($b) => $b->delete());
                $model->delete();

                return true;
            });
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * True if the trainer already has another session overlapping [start, end).
     */
    public function trainerHasOverlap(int $trainerId, DateTimeInterface $start, DateTimeInterface $end, ?int $exceptScheduleId = null): bool
    {
        return ClassSchedule::query()
            ->where('trainer_id', $trainerId)
            ->when($exceptScheduleId, fn ($q, $id) => $q->where('id', '!=', $id))
            ->where('starts_at', '<', $end)
            ->where('ends_at', '>', $start)
            ->exists();
    }

    public function assertNoTrainerOverlap(int $trainerId, DateTimeInterface $start, DateTimeInterface $end, ?int $exceptScheduleId): void
    {
        if ($this->trainerHasOverlap($trainerId, $start, $end, $exceptScheduleId)) {
            throw new \Exception(__('This trainer is already assigned to another session during that time.'));
        }
    }

    protected function assertTrainerRole(int $userId): void
    {
        if (! User::query()->where('id', $userId)->where('role', 'trainer')->exists()) {
            throw new \Exception(__('Selected user must be a trainer.'));
        }
    }
}
