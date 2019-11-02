<?php

namespace App\Mail;

use Illuminate\Support\Facades\Mail;

class CommonMail
{
    const TO_MAIL = '1012585365@qq.com';

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
    public function build($msg)
    {

        $subject = '邮件名称';
        $to = self::TO_MAIL;
        Mail::send(
            'emails.normal',
            ['msg' => $msg],
            function ($message) use($to, $subject) {
                $message->to($to)->subject($subject);
            }
        );
    }

    /**
     * @param $msg
     */
    public function warnMail($msg)
    {
        $subject = '异常警报';
        $to = self::TO_MAIL;
        Mail::send(
            'emails.normal',
            ['msg' => $msg],
            function ($message) use($to, $subject) {
                $message->to($to)->subject($subject);
            }
        );
    }



}
