<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Resource extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'name',
        'type',
        'description',
        'color',
        'working_hours',
        'is_active',
    ];

    protected $casts = [
        'working_hours' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the tenant that owns the resource.
     */
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the appointments for the resource.
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
}
