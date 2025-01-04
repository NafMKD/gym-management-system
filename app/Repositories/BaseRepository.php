<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;

abstract class BaseRepository
{
    /**
     * Store a newly created resource in storage.
     *
     * @param array $attributes
     * @return mixed
     */
    abstract public function store(array $attributes): bool;

    /**
     * Update the specified resource in storage.
     *
     * @param mixed $model
     * @param array $attributes
     * @return bool
     */
    abstract public function update(mixed $model, array $attributes): mixed;

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
            throw $e;
        }
    }
}
