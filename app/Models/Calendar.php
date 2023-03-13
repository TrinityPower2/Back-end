<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Calendar extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_calendar';

    public $timestamps = false;

    protected $fillable = [
        'name_calendar',
        'to_notify'
    ];
}
