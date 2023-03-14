<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class To_do_list extends Model
{
    use HasFactory;

    protected $primaryKey = 'id_todo';

    public $timestamps = false;

    protected $fillable = [
        'name_todo',
        'id_users'
    ];
}
