<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TeamLeader extends Model
{
    protected $table = 'team_leader_reviews';

    protected $fillable = [
        'tl_id',
        'employee_name',
        'performance',
        'comment'
    ];
}