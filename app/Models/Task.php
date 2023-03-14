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
        'date_day',
        'id_todo'
    ];
}
