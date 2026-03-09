<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notice extends Model
{
        // Explicitly tell Laravel which table to use
    protected $table = 'notice';
    protected $fillable = [
        'title',
        'description',
        'created_by',
        'expired_at',
        'type',
        'status',
        'image',
        'notice_users',
        'notice_type',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'expired_at' => 'datetime',
        'notice_users' => 'array',
    ];


public static function getByIdSelected($id)
{
    return self::select('id', 'title', 'description', 'status', 'notice_type','image')
        ->find($id);
}

   
}

