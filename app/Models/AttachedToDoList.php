<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttachedToDoList extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_att_todo';

    public $timestamps = false;

    protected $fillable = [
        'name_todo',
        'id_event'
    ];
}
