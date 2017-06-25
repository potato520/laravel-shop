<?php

namespace App\Http\Controllers\Service;

use App\Models\Member;
use App\Models\TempEmail;
use App\Tool\ValidateCode\ValidateCode;

use App\Http\Controllers\Controller;
use App\Tool\SMS\SendTemplateSMS;
use App\Models\TempPhone;
use Illuminate\Http\Request;
use App\Models\M3Result;

class ValidateCodeController extends Controller
{
    // 创建验证码
   public function create(Request $request){
       $validateCode = new ValidateCode;
       //存储验证码到session
       $request->session()->put('validate', $validateCode->getCode());
       return $validateCode->doimg();
   }

   // 发送短信
    public function sendSMS(Request $request)
    {
        $m3_result = new M3Result;

        // 获取手机号码
        $phone = $request->input('phone', '');

        if($phone == ''){
            $m3_result->status = 1;
            $m3_result->message = '手机号不能为空';
            return $m3_result->toJson(); # 返回json 状态
        }
        if(strlen($phone) != 11 || $phone[0] != '1'){
            $m3_result->status = 2;
            $m3_result->message = '手机格式不正确';
            return $m3_result->toJson();
        }

        $charset = '1234567890';
        $code = '';
        $_len = strlen($charset) - 1;
        for ($i = 0;$i < 4;++$i) {
            $code .= $charset[mt_rand(0, $_len)];
        }

        $SendTemplateSMS = new SendTemplateSMS;
                                        // 手机号码， 验证码，有效时间，1：是默认免费模板
        $m3_result = $SendTemplateSMS->sendTemplateSMS($phone, array($code, 60), 1);
        // 如果发送成功短信
        if($m3_result->status == 0){
            // 检查是否已经注册过的手机号码
            $tempPhone = TempPhone::where('phone', '=', $phone)->first();
            if($tempPhone == null){
                $tempPhone = new TempPhone();
            }

            // 保存数据到表中
            $tempPhone->phone = $phone;
            $tempPhone->code = $code;
            $tempPhone->deadline = date('Y-m-d H-i-s', time() + 60*60); # 验证码的过期时间是 60分钟
            $tempPhone->save();
        }

        return $m3_result->toJson();

    }

    // 验证邮箱
    public function validateEmail(Request $request)
    {
        $member_id = $request->input('member_id', '');
        $code = $request->input('code', '');

        $tempEmail = TempEmail::where('member_id', '=', $member_id)->first();
        if($tempEmail == null){
            return '验证异常';
        }

        // 验证验证码的是否存在
        if($tempEmail->code == $code){
            // 验证码的有效时间
            if(time() > strtotime($tempEmail->deadline)){
                return '此链接已经失效，请重新获取邮箱验证码';
            }

            // 开始激活邮箱账户
            $member = Member::find($member_id);
            $member->active = 1;
            $member->save();

            return redirect('/login');
        }else{
            return '链接已经失效，请重新获取邮箱验证码';
        }


    }
}
