<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, HelperTrait, SoftDeletes;

    /**
     * Session authentication uses the default Eloquent user provider: pass
     * `['phone' => local 10-digit, 'password' => ...]` to Auth::attempt().
     * The password reset broker still identifies users by email when sending links.
     */

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'phone',
        'role',
        'gender'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the proper name of the user 
     * 
     * @return string 
     */
    public function getName() : string 
    {
        return ucwords($this->first_name . " " . $this->last_name);
    }

    /**
     * Get all Memberships for this User
     *
     * @return HasMany
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id');
    }

    /**
     * Trainer-only extended profile (qualifications, default session commission, etc.).
     */
    public function trainerProfile(): HasOne
    {
        return $this->hasOne(TrainerProfile::class, 'user_id');
    }

    /**
     * Commission ledger rows where this user is the trainer.
     */
    public function trainerCommissionEntries(): HasMany
    {
        return $this->hasMany(TrainerCommissionEntry::class, 'trainer_id');
    }

    /**
     * Get all Audit Trail for this User
     *
     * @return HasMany
     */
    public function auditTrail(): HasMany
    {
        return $this->hasMany(Membership::class, 'user_id');
    }
}
