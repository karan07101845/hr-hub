<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes; 


class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

    protected $fillable = [
        'name',
        'email',
        'password',
        'image',
        'role',
        'team_id',
        'manager_id',
        'designation',
        'last_seen_public_notice_id',
        'joining_date',
        'aadhaar_number',
        'emp_code',
        'pan_number',
        'father_name',
        'mother_name',
        'years_of_experience',
        'training_experience',
        'previous_company_name',
        'previous_designation',
        'previous_company_duration',
        'document'
    ];

        protected $dates = ['deleted_at'];

        protected $casts = [
    'document' => 'array',
];


    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // auto-hash password
            'joining_date' => 'date',
            'years_of_experience' => 'integer',
            'previous_company_duration' => 'integer',
             'deleted_at' => 'datetime', 

        ];
    }


    public function manager()
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function employees()
    {
        return $this->hasMany(User::class, 'manager_id');
    }
}
