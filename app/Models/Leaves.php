<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Leaves extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'manager_id',
        'approved_by',
        'leave_type',
        'start_date',
        'end_date',
        'reason',
        'remarks',
        'status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }
}