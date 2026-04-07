<?php

namespace App\Repositories;

use App\Models\User;
use App\Support\PhoneNumber;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserRepository extends BaseRepository {

    /**
     * Store a new user in the database.
     *
     * @param array $attributes
     * @return bool
     */
    public function store(array $attributes): mixed
    {
        try {
            DB::transaction(function () use ($attributes) {
                $email = $attributes['email'] ?? null;
                if ($email === '') {
                    $email = null;
                }

                $validatedAttributes = [
                    'first_name' => $attributes['first_name'] ?? null,
                    'last_name' => $attributes['last_name'] ?? null,
                    'email' => $email,
                    'password' => $attributes['password'] ?? null,
                    'phone' => $attributes['phone'] ?? null,
                    'role' => $attributes['role'] ?? null,
                    'gender' => $attributes['gender'] ?? null,
                ];

                if (! isset($validatedAttributes['first_name'], $validatedAttributes['last_name'], $validatedAttributes['password'], $validatedAttributes['phone'], $validatedAttributes['role'], $validatedAttributes['gender'])) {
                    throw new \Exception('Missing required attributes.');
                }

                if (! PhoneNumber::isValid((string) $validatedAttributes['phone'])) {
                    throw new \Exception(__('Invalid phone number format.'));
                }

                $validatedAttributes['password'] = Hash::make($validatedAttributes['password']);
                User::create($validatedAttributes);
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
            /** @var User $model */
            $updateData = [];

            foreach (['first_name', 'last_name', 'email', 'phone', 'role', 'gender'] as $key) {
                if (! array_key_exists($key, $attributes)) {
                    continue;
                }

                $value = $attributes[$key];
                if ($key === 'email') {
                    $value = $value === '' ? null : $value;
                    if ($model->email === $value) {
                        continue;
                    }
                    $updateData['email'] = $value;

                    continue;
                }

                if ($key === 'phone') {
                    if (! PhoneNumber::isValid((string) $value)) {
                        throw new \Exception(__('Invalid phone number format.'));
                    }
                    if ((string) $model->phone === (string) $value) {
                        continue;
                    }
                    $updateData['phone'] = $value;

                    continue;
                }

                if ($model->{$key} == $value) {
                    continue;
                }
                $updateData[$key] = $value;
            }

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
            throw $e;
        }
    }
}
