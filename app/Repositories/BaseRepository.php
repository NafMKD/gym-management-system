<?php

namespace App\Repositories;


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
     * Remove the specified resource from storage.(SoftDelete)
     *
     * @param mixed $model
     * @return bool
     */
    abstract public function destroy(mixed $model): bool;
}
