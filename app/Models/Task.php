<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_task';

    public $timestamps = false;

    protected $fillable = [
        'name_task',
        'description',
        'id_todo',
        'priority_level',
        'is_done',
        'id_buddy'
    ];
}
