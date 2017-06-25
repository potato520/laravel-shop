<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\M3Result;
use Illuminate\Http\Request;


class BookController extends Controller
{
    public function getCategoryByParentId(Request $request)
    {
        $parent_id = $request->input('parent_id', 0);
        $categorys = Category::where('parent_id', '=', $parent_id)->get();

        $m3_result = new M3Result;
        $m3_result->status = 0;
        $m3_result->message = '返回成功';
        $m3_result->categorys = $categorys;

        return $m3_result->toJson();
    }
}
