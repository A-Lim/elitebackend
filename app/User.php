<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

use Laravel\Passport\HasApiTokens;
use App\Notifications\CustomResetPassword;
use App\Notifications\CustomVerifyEmail;
use App\Http\Traits\HasUserGroups;
use App\Http\Traits\CustomQuery;

class User extends Authenticatable {
    use Notifiable, HasApiTokens, HasUserGroups, CustomQuery;

    protected $fillable = ['name', 'email', 'password', 'avatar', 'email_verified_at', 'status'];
    protected $hidden = ['password', 'remember_token', 'created_at', 'updated_at'];
    protected $casts = ['email_verified_at' => 'datetime'];

    // list of properties queryable for datatable
    public static $queryable = ['name', 'email', 'status'];

    const STATUS_ACTIVE = 'active';
    const STATUS_LOCKED = 'locked';
    const STATUS_UNVERIFIED = 'unverified';

    const STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_LOCKED,
        self::STATUS_UNVERIFIED,
    ];

    /**
     * Model events
     *
     * @return void
     */
    public static function boot() {
        parent::boot();

        self::creating(function($model) {
            // if status is not provided
            // set default to unverified
            if (empty($model->status)) {
                $model->status = self::STATUS_UNVERIFIED;
            }
        });
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token) {
        $this->notify(new CustomResetPassword($token));
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification() {
        $this->notify(new CustomVerifyEmail($this->email));
    }

    /**
     * Mark email as verified (email_verified_at, status)
     *
     * @return void
     */
    public function markEmailAsVerified() {
        return $this->forceFill([
            'email_verified_at' => $this->freshTimestamp(),
            'status' => self::STATUS_ACTIVE,
        ])->save();
    }

    /**
     * Checks if user is verified
     *
     * @return boolean
     */
    public function hasVerifiedEmail() {
        return $this->status != self::STATUS_UNVERIFIED || $this->email_verified_at != null;
    }

    /******** Accessors and Mutators ********/

    /**
     * Convert json string to an array with all the image paths
     * for avatar image
     *
     * @return array
     */
    public function getAvatarAttribute($value) {
        if ($value != null) 
            return json_decode($value);
        
        return [];
    }
}
