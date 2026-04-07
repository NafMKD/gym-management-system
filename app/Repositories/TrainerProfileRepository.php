<?php

namespace App\Repositories;

use App\Models\TrainerProfile;
use Illuminate\Support\Facades\DB;

class TrainerProfileRepository extends BaseRepository
{
    /**
     * @param array<string, mixed> $attributes
     */
    public function store(array $attributes): mixed
    {
        return DB::transaction(function () use ($attributes) {
            $validated = [
                'user_id' => $attributes['user_id'] ?? null,
                'qualifications' => $attributes['qualifications'] ?? null,
                'specializations' => $attributes['specializations'] ?? null,
                'bio' => $attributes['bio'] ?? null,
                'commission_per_session' => $attributes['commission_per_session'] ?? 0,
            ];

            if (empty($validated['user_id'])) {
                throw new \Exception('Trainer user is required.');
            }

            return TrainerProfile::create($validated);
        });
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function update(mixed $model, array $attributes): mixed
    {
        return DB::transaction(function () use ($model, $attributes) {
            /** @var TrainerProfile $model */
            $original = $model->only([
                'qualifications', 'specializations', 'bio', 'commission_per_session',
            ]);

            $updateData = array_filter(
                $attributes,
                fn ($value, $key) => array_key_exists($key, $original) && $value != $original[$key],
                ARRAY_FILTER_USE_BOTH
            );

            if (empty($updateData)) {
                throw new \App\Exceptions\NoUpdateNeededException;
            }

            $model->update($updateData);

            return $model->fresh();
        });
    }
}
