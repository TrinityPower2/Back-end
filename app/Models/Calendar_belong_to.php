<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar_belong_to extends Model
{
    use HasFactory;

    public $timestamps = false;

    protected $fillable = [
        'id_users',
        'id_calendar'
    ];
}
