<?php
/**
 * 验证码
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */
use Shopnc\Tpl;


defined('InShopNC') or exit('Access Invalid!');

class seccodeControl{
    public function __construct(){
    }

    /**
     * 产生验证码
     *
     */
    public function makecodeOp(){
        $refererhost = parse_url($_SERVER['HTTP_REFERER']);
        $refererhost['host'] .= !empty($refererhost['port']) ? (':'.$refererhost['port']) : '';

        $seccode = makeSeccode($_GET['nchash']);

        @header("Expires: -1");
        @header("Cache-Control: no-store, private, post-check=0, pre-check=0, max-age=0", FALSE);
        @header("Pragma: no-cache");

        $width = 26;
        $height = 90;
        if ($_GET['type']) {
            $param = explode(',', $_GET['type']);
            $width = intval($param[0]);
            $height = intval($param[1]);
        }
        echo \Shopnc\Lib::imager()->createCaptcha($seccode, $height, $width);
    }

    /**
     * AJAX验证
     *
     */
    public function checkOp(){
        if (checkSeccode($_GET['nchash'],$_GET['captcha'])){
            exit('true');
        }else{
            exit('false');
        }
    }
}
