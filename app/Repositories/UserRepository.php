<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository {

    /**
     * Store a new user in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function store(array $attributes): bool
    {
        try {
            DB::transaction(function () use ($attributes) {
                $validatedAttributes = [
                    'name' => $attributes['name'] ?? null,
                    'email' => $attributes['email'] ?? null,
                    'password' => $attributes['password'] ?? null,
                    'phone' => $attributes['phone'] ?? null,
                    'role' => $attributes['role'] ?? null,
                    'gender' => $attributes['gender'] ?? null,
                ];

                if (!isset($validatedAttributes['name'], $validatedAttributes['email'], $validatedAttributes['password'], $validatedAttributes['role'], $validatedAttributes['gender'])) {
                    throw new \Exception("Missing required attributes.");
                }

                $validatedAttributes['password'] = Hash::make($validatedAttributes['password']);
                User::create($validatedAttributes);
            });
            return true;
        } catch (\Exception $e) {
            // TODO: Log the error or handle exception as needed
            return false;
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
            $originalAttributes = $model->only(['name', 'email', 'phone', 'role', 'gender']);

            $updateData = array_filter(
                $attributes,
                fn($value, $key) => array_key_exists($key, $originalAttributes) && $value !== $originalAttributes[$key],
                ARRAY_FILTER_USE_BOTH
            );

            if (isset($attributes['password'])) {
                $updateData['password'] = Hash::make($attributes['password']);
            }

            if (empty($updateData)) {
                throw new \App\Exceptions\NoUpdateNeededException();
            }

            DB::transaction(function () use ($model, $updateData) {
                $model->update($updateData);
            });

            return $model;
        } catch (\Exception $e) {
            // TODO: Log the error or handle exception as needed
            return null;
        }
    }

    /**
     * Soft delete a user from the database.
     *
     * @param mixed $model
     * @return bool
     */
    public function destroy(mixed $model): bool
    {
        try {
            DB::transaction(function () use ($model) {
                $model->delete();
            });
            return true;
        } catch (\Exception $e) {
            // TODO: Log the error or handle exception as needed
            return false;
        }
    }
}
