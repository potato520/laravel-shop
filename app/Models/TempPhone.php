<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempPhone extends Model
{
    protected $table = 'temp_phone';
    protected $primarykey = 'id';

    //不需要时间戳
    public $timestamps = false;
}
