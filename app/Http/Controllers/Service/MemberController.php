<?php

namespace App\Http\Controllers\Service;

use App\Http\Controllers\Controller;

use App\Models\M3Email;
use App\Models\M3Result;
use App\Models\Member;
use App\Models\TempEmail;
use App\Models\TempPhone;
use App\Tool\UUID;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MemberController extends Controller
{
    public function register(Request $request){
        $email = $request -> input('email', '');
        $phone = $request -> input('phone', '');
        $password = $request -> input('password', '');
        $confirm = $request -> input('confirm', '');
        $phone_code = $request -> input('phone_code', '');
        $validate_code = $request -> input('validate_code', '');

        $m3_result = new M3Result();

        if($email == '' && $phone == '') {
            $m3_result->status = 1;
            $m3_result->message = '手机号或邮箱不能为空';
            return $m3_result->toJson();
        }
        if($password == '' || strlen($password) < 6) {
            $m3_result->status = 2;
            $m3_result->message = '密码不少于6位';
            return $m3_result->toJson();
        }
        if($confirm == '' || strlen($confirm) < 6) {
            $m3_result->status = 3;
            $m3_result->message = '确认密码不少于6位';
            return $m3_result->toJson();
        }
        if($password != $confirm) {
            $m3_result->status = 4;
            $m3_result->message = '两次密码不相同';
            return $m3_result->toJson();
        }

        // 手机号注册
        if($phone != '') {
            if($phone_code == '' || strlen($phone_code) != 4) {
                $m3_result->status = 5;
                $m3_result->message = '手机验证码为4位';
                return $m3_result->toJson();
            }
            #手机号码逻辑
            $tempPhone = TempPhone::where('phone', '=', $phone)->first();

            // 如果数据库中的手机验证码 等于 我获取的验证码
            if($tempPhone->code == $phone_code){
                // 判断验证码的有效期
                if(time() > strtotime($tempPhone->deadline)){ # 将时间字符串转换成时间戳
                    $m3_result->status = 7;
                    $m3_result->message = '验证码不正确或已过期，重新获取';
                    return $m3_result->toJson();
                }
                // 验证码正确，并且没有过期
                $member = new Member;
                $member->phone = $phone;
                $member->password = md5('tk' + $password);
                $member->save();

                $m3_result->status = 0;
                $m3_result->message = '注册成功';
                return $m3_result->toJson();
            }else{
                $m3_result->status = 7;
                $m3_result->message = '验证码不正确或已过期，重新获取';
                return $m3_result->toJson();
            }

            // 邮箱注册
        } else {
            if ($validate_code == '' || strlen($validate_code) != 4) {
                $m3_result->status = 6;
                $m3_result->message = '验证码为4位';
                return $m3_result->toJson();
            }

            // 获取session 中的验证码，如果没有默认为空字符串
            $validate_code_session = $request->session()->get('validate', '');

            // 如果session 中的验证和 表单接收的验证码不一致
            if($validate_code_session != $validate_code){
                $m3_result->status = 8;
                $m3_result->message = '验证码错误';
                return $m3_result->toJson();
            }

            // 保存邮箱注册信息
            $member = new Member;
            $member->email = $email;
            $member->password = md5('tk' + $password);
            $member->save();

            $uuid = UUID::create();

            /*发送激活邮件*/
            $m3_email = new M3Email;
            $m3_email->to = $email;
            $m3_email->cc = '573358951@qq.com';
            $m3_email->subject = '凯恩书店欢迎你';
            $m3_email->content = '请在24小时之内完成验证. http://127.0.0.1/hxdy/public/service/validate_email'
                                . '?member_id=' . $member->id
                                . '&code=' . $uuid;
            // 保存数据到邮箱验证表中
            $tempEmail = new TempEmail;
            $tempEmail->member_id = $member->id;
            $tempEmail->code = $uuid;
            $tempEmail->deadline = date('Y-m-d H-i-s', time() + 2460*60); # 邮箱验证码的有效时间 24小时
            $tempEmail->save();

            Mail::send('email_register', ['m3_email' => $m3_email], function ($m) use ($m3_email){
                $m->to($m3_email->to, '尊敬的用户')
                    ->cc($m3_email->cc)
                    ->subject($m3_email->subject);
            });
            Mail::send('email_register', ['m3_email' => $m3_email], function ($m) use ($m3_email) {
                // $m->from('hello@app.com', 'Your Application');
                $m->to($m3_email->to, '尊敬的用户')
                    ->cc($m3_email->cc)
                    ->subject($m3_email->subject);
            });



            $m3_result->status = 0;
            $m3_result->message = '注册成功';
            return $m3_result->toJson();
        }


    }

    // 登录
    public function login(Request $request){
        $m3_result = new M3Result;

        $username = $request->input('username', '');
        $password = $request->input('password', '');
        $validate_code = $request->input('validate_code', '');

        // 检测验证码是否正确
        $validate_code_session = $request->session()->get('validate');
        if($validate_code_session != $validate_code){
            $m3_result->status = 1;
            $m3_result->message = '验证码错误';
            return $m3_result->toJson();
        }

        // 字符串中是否包含@
        if(strpos($username, '@') == true) {
            $member = Member::where('email', '=', $username)->first();
        }else{
            $member = Member::where('phone', '=', $username)->first();
        }

        if($member == null){
            $m3_result->status = 2;
            $m3_result->message = '该用户不存在';
            return $m3_result->toJson();
        }else{
            if($member->password != md5('tk' + $password)){
                $m3_result->status = 3;
                $m3_result->message = '密码不正确';
                return $m3_result->toJson();
            }
        }

        // 登录成功
        $request->session()->put('member', $member);
        $request->session()->put('member_id', $member->id);

        $m3_result->status = 0;
        $m3_result->message = '登录成功';
        return $m3_result->toJson();

    }

    public function toRegister(){
        return view('register');
    }
}
