<?php

/**
 * 公用的方法  返回json数据，进行信息的提示
 * @param $status 状态
 * @param string $message 提示信息
 * @param array $data 返回数据
 */
function mdate($str, $timestamp = 0)
{
    date_default_timezone_set("PRC");
    if (empty($timestamp)) {
        $timestamp = time();
    }
    return date($str, $timestamp);
}