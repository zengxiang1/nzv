<?php
/**
 *
 * QQ,新浪微博登陆
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net/
 * @link       http://www.shopnc.net/
 * @since      File available since Release v1.1
 */
use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');
class connectControl extends mobileHomeControl{
	
	
	/**
     * 新浪微博登陆
     */
    public function get_sina_oauth2Op() {
		$code_url = MEMBER_SITE_URL.'/api.php?act=tosina&state=api&display=mobile';
		@header("location:$code_url");
	}

	/**
     * QQ登陆
     */
    public function get_qq_oauth2Op() {
		$code_url = MEMBER_SITE_URL.'/api.php?act=toqq';
		@header("location:$code_url");
	}
}
