<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'city',
        'state',
        'zip_code',
        'logo',
        'description',
        'business_type',
        'subscription_status',
        'trial_ends_at',
        'subscription_ends_at',
        'stripe_customer_id',
        'stripe_subscription_id',
        'is_active',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the services for the tenant.
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the resources for the tenant.
     */
    public function resources()
    {
        return $this->hasMany(Resource::class);
    }

    /**
     * Get the appointments for the tenant.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Get the users for the tenant.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
