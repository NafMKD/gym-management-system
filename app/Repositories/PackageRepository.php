<?php

namespace App\Repositories;

use App\Models\Package;
use Illuminate\Support\Facades\DB;

class PackageRepository extends BaseRepository {

    /**
     * Store a new user in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function store(array $attributes): bool
    {
        try {
            DB::transaction(callback: function () use ($attributes) {
                $validatedAttributes = [
                    'name' => $attributes['name'] ?? null,
                    'price' => $attributes['price'] ?? null,
                    'duration' => $attributes['duration'] ?? null,
                    'description' => $attributes['description'] ?? null,
                ];

                if (!isset($validatedAttributes['name'], $validatedAttributes['price'], $validatedAttributes['duration'])) {
                    throw new \Exception("Missing required attributes.");
                }

                Package::create($validatedAttributes);
            });

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing user in the database.
     *
     * @param mixed $model
     * @param array $attributes
     * @return mixed
     */
    public function update(mixed $model, array $attributes): mixed
    {
        try {
            DB::transaction(function () use ($model, $attributes) {
                $originalAttributes = $model->only(['name', 'price', 'duration', 'description']);

                $updateData = array_filter(
                    $attributes,
                    fn($value, $key) => isset($originalAttributes[$key]) && $value != $originalAttributes[$key],
                    ARRAY_FILTER_USE_BOTH
                );

                if (empty($updateData)) {
                    throw new \App\Exceptions\NoUpdateNeededException();
                }

                DB::transaction(function () use ($model, $updateData) {
                    $model->update($updateData);
                });

                return $model;
            });

            return $model;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}