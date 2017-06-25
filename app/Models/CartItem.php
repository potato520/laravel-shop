<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $table = 'cart_item';
    protected $primarykey = 'id';

    //不需要时间戳
    public $timestamps = false;
}
