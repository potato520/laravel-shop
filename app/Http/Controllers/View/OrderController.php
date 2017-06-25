<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;


class OrderController extends Controller
{
    public function toOrderPay(Request $request)
    {
        # $request->session()->forget('member');
        return view('order_pay');
    }

}