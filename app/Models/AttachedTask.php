<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachedTask extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_att_task';

    public $timestamps = false;

    protected $fillable = [
        'name_task',
        'description',
        'date_day',
        'id_todo',
        'priority_level',
        'is_done'
    ];
}
