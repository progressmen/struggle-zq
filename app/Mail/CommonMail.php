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
     * @param $msg
     * @return $this
     */
    public function normalMail($msg)
    {

        $subject = 'struggle';
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
            'emails.warn',
            ['msg' => $msg],
            function ($message) use($to, $subject) {
                $message->to($to)->subject($subject);
            }
        );
    }



}
