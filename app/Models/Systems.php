<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Systems extends Model
{
        use HasFactory;

      protected $fillable = [
        'team_id',
        'upwork_id',
        'system_users',
        'employee_id'
    ];
}
