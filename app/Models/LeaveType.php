<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'days_entitled',
        'min_days',
        'max_days',
        'is_paid',
        'requires_approval',
        'is_active',
        'metadata',
    ];

    protected $casts = [
        'days_entitled' => 'integer',
        'min_days' => 'integer',
        'max_days' => 'integer',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'is_active' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get leaves of this type
     */
    public function leaves()
    {
        return $this->hasMany(Leave::class, 'leave_type', 'code');
    }

    /**
     * Scope to get only active leave types
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Get the effective days for this leave type
     * Returns the entitled days, or a range if min/max are set
     */
    public function getEffectiveDaysAttribute(): string
    {
        if ($this->min_days && $this->max_days) {
            return "{$this->min_days}-{$this->max_days} days";
        }
        if ($this->days_entitled) {
            return "{$this->days_entitled} days";
        }
        return 'Variable';
    }
}
