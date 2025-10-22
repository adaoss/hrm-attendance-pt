<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'settings',
        'is_active',
    ];

    protected $casts = [
        'settings' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get all users belonging to this team
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get all employees belonging to this team
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Get all departments belonging to this team
     */
    public function departments()
    {
        return $this->hasMany(Department::class);
    }

    /**
     * Get all work schedules belonging to this team
     */
    public function workSchedules()
    {
        return $this->hasMany(WorkSchedule::class);
    }
}
