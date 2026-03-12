<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Notifications extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'message',
        'link',
        'is_read'
    ];
}