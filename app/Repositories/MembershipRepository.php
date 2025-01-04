<?php

namespace App\Repositories;

use App\Models\Membership;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class MembershipRepository extends BaseRepository {

    /**
     * Store a new membership in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function store(array $attributes): bool
    {
        try {
            DB::transaction(function () use ($attributes) {
                $validatedAttributes = [
                    'user_id' => $attributes['user_id'] ?? null,
                    'start_date' => $attributes['start_date'] ?? null,
                    'end_date' => $attributes['end_date'] ?? null,
                    'package_id' => $attributes['package_id'] ?? null,
                    'remaining_days' => $attributes['remaining_days'] ?? null,
                    'status' => $attributes['status'] ?? 'inactive',
                    'price' => $attributes['price'] ?? null,
                ];

                
                if (!isset($validatedAttributes['user_id'], $validatedAttributes['start_date'], $validatedAttributes['end_date'], $validatedAttributes['remaining_days'], $validatedAttributes['status'], $validatedAttributes['price'])) {
                    throw new \Exception("Missing required attributes.");
                }

                $user = User::find($validatedAttributes['user_id']);

                if ($user->memberships()->where('status', 'active')->exists()) {
                    throw new \Exception("User already has an active membership.");
                }

                Membership::create($validatedAttributes);
            });

            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Update an existing membership in the database.
     *
     * @param mixed $model
     * @param array $attributes
     * @return mixed
     */
    public function update(mixed $model, array $attributes): mixed
    {
        return null;
    }
}
