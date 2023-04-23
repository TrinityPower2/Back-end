<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_event';

    public $timestamps = false;

    protected $fillable = [
        'name_event',
        'description',
        'start_date',
        'length',
        'movable',
        'priority_level',
        'id_calendar',
        'to_repeat',
        'color'
    ];
}
