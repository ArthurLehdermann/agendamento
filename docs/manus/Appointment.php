<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Appointment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'service_id',
        'resource_id',
        'user_id',
        'start_time',
        'end_time',
        'status',
        'notes',
        'customer_name',
        'customer_email',
        'customer_phone',
        'custom_fields',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'custom_fields' => 'array',
    ];

    /**
     * Get the tenant that owns the appointment.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the service associated with the appointment.
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get the resource associated with the appointment.
     */
    public function resource()
    {
        return $this->belongsTo(Resource::class);
    }

    /**
     * Get the user associated with the appointment.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
