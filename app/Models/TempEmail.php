<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TempEmail extends Model
{
    protected $table = 'temp_email';
    protected $primarykey = 'id';

    //不需要时间戳
    public $timestamps = false;
}
