<?php

namespace App\Mail;

use Illuminate\Support\Facades\Mail;

class CommonMail
{

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
//        $message = 'test';
        $to = '1012585365@qq.com';
        $subject = '邮件名称';
        Mail::send(
            'emails.test',
            ['name' => 'test'],
            function ($message) use($to, $subject) {
                $message->to($to)->subject($subject);
            }
        );
    }


    /*public function send_email(Request $request){
        header("Access-Control-Allow-Origin: *");    //跨域
        $leaveMsg = $request->input('leaveMsg')?:'空';
        $email = $request->input('email')?:'空';
        $name = $request->input('name')?:'空';
        $number = $request->input('number')?:'空';
        $content =['姓名'=>$name,'电话'=>$number,'邮箱'=>$email,'留言'=>$leaveMsg];
        $send = [
            'email'=>[
                '1234567890@qq.com',
                '87654321@163.com'
            ],
            'name'=>'邮件标题',
            'content'=>$content
        ];
        //emails.send_email 为 resources/views/emails/send_email.blade.php
        //引用的Mail类为php中的（use Mail）或者laravel中的门面类（use Illuminate\Support\Facades\Mail;)
        Mail::send('emails.send_email', $send, function($message) use($send)
        {
            $emailArr = array_filter($send['email']);   //去空值
            foreach ($emailArr as $email){
                $email ? $message->to($email)->subject($send['name']) : '';
            }
        });
        return $data;
    }*/

}
