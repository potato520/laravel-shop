<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('login');

//    return \App\Models\Member::all();
});


Route::get('/login', 'View\MemberController@toLogin');

Route::get('/register', 'View\MemberController@toRegister');
Route::get('/category', 'View\BookController@toCategory');
Route::get('/product/category_id/{category_id}', 'View\BookController@toProduct');
Route::get('/product/{product_id}', 'View\BookController@toPdtContent');
Route::get('/cart', 'View\CartController@toCart');
# 验证登录的中间件，手册
//Route::get('/cart', 'View\CartController@toCart', function (){})->middleware('check.login');

Route::group(['middleware' => ['check.login']], function () {
    Route::get('/order_pay', 'View\OrderController@toOrderPay');

});


// 分组 [自动带上 service 的前缀]
Route::group(['prefix' => 'service'], function (){
    Route::get('validate_code/create', 'Service\ValidateCodeController@create');
    Route::post('validate_phone/send', 'Service\ValidateCodeController@sendSMS');
    Route::any('validate_email', 'Service\ValidateCodeController@validateEmail');
    Route::post('register', 'Service\MemberController@register');
    Route::post('login', 'Service\MemberController@login');
    Route::post('category/parent_id', 'Service\BookController@getCategoryByParentId');
    Route::get('cart/add/{product_id}', 'Service\CartController@addCart');
    Route::get('cart/delete', 'Service\CartController@deleteCart'); // 删除购物车商品
});


