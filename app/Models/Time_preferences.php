<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Time_preferences extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_timepref';

    public $timestamps = false;

    protected $fillable = [
        'name_timepref',
        'start_time',
        'length',
        'id_users'
    ];
}
