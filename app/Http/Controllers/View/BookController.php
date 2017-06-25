<?php

namespace App\Http\Controllers\View;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\PdtContent;
use App\Models\PdtImages;
use App\Models\Product;
use Illuminate\Http\Request;
use App\Models\CartItem;

class BookController extends Controller
{
    public function toCategory()
    {
        $categorys = Category::where('parent_id', '=', 0)->get();
        return view('category', compact('categorys'));
    }

    public function toProduct($category_id)
    {
        $products = Product::where('category_id', '=', $category_id)->get();
        return view('product', compact('products'));
    }

    public function toPdtContent(Request $request, $product_id)
    {
        $product = Product::find($product_id);
        $pdt_content = PdtContent::where('product_id', $product_id)->first();
        $pdt_images = PdtImages::where('product_id', $product_id)->get();

        $count = 0;

        $member = $request->session()->get('member', '');
        if($member != '') {
            $cart_items = CartItem::where('member_id', $member->id)->get();

            foreach ($cart_items as $cart_item) {
                if($cart_item->product_id == $product_id) {
                    $count = $cart_item->count;
                    break;
                }
            }
        } else {
            $bk_cart = $request->cookie('bk_cart');
            $bk_cart_arr = ($bk_cart!=null ? explode(',', $bk_cart) : array());

            foreach ($bk_cart_arr as $value) {   // 一定要传引用
                $index = strpos($value, ':');
                if(substr($value, 0, $index) == $product_id) {
                    $count = (int) substr($value, $index+1);
                    break;
                }
            }
        }

        return view('pdt_content', compact('product', 'pdt_content', 'pdt_images', 'count'));
    }


}