<?php session_start();

/**
*	wap端手机登录
* @author zx
*/

use Shopnc\Tpl;
defined('InShopNC') or exit('Access Invalid!');
class connect_smsControl extends mobileControl{
	/**
    *
    *注册
    *@author zx
    */
    public function registerOp(){
        $pattern = "/^([0-9A-Za-z\\-_\\.]+)@([0-9a-z]+\\.[a-z]{2,3}(\\.[a-z]{2})?)$/i";
         if (!preg_match( $pattern, $_POST['email'] ) ){
                 $state = '邮箱格式错误，重新输入';
                 exit($state);
            }
        $model_member = Model('member');
        $phone = $_POST['register_phone'];
        $captcha = $_POST['register_captcha'];
        if (strlen($phone) == 11 && strlen($captcha) == 6){
            if(C('sms_register') != 1) {
                $state = '系统没有开启手机注册功能';
                exit($state);
            }
            $member_name = $_POST['member_name'];
            $member = $model_member->getMemberInfo(array('member_name'=> $member_name));//检查重名
            if(!empty($member)) {
                $state  = '用户名已被注册';
                exit($state);
            }
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {
                $state = '手机号已被注册';
                exit($state);
            }
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 1;
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
               $state = '动态码错误或已过期，重新输入';
               exit($state);
            }
            
            $member = array();
            $member['member_name'] = $member_name;
            $member['member_passwd'] = $_POST['password'];
            $member['member_email'] = $_POST['email'];
            
            $member['member_mobile'] = $phone;
            $member['member_mobile_bind'] = 1;
            $result = $model_member->addMember($member);
            if($result) {
                $member = $model_member->getMemberInfo(array('member_name'=> $member_name));
                $token = $this->_get_token($member['member_id'], $member['member_name'], $_POST['client']);
                setcookie('username',$member_info['member_name'],time()+3600,'/');
                setcookie('key',$token,time()+3600,'/');
                //返回true证明登录成功
                $state = 'true';
            } else {
                $state = '注册失败';
            }
        } else {
            $state = '注册失败';
        }
        exit($state);
    }
/**
    *获得验证码
    */
	public function get_captchaOp(){
        $state = '发送失败';
        $phone = $_GET['phone'];
        //如果登录端传来的是client是wap 如果不是则错误
        if (($_GET['client']=='wap'?true:false) && strlen($phone) == 11){
            $log_type = $_GET['type'];//短信类型:1为注册,2为登录,3为找回密码
            $state = 'true';
            $model_sms_log = Model('sms_log');
            $condition = array();
            $condition['log_ip'] = getIp();
            $condition['log_type'] = $log_type;
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(!empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP-600)) {//同一IP十分钟内只能发一条短信
                $state = '同一IP地址十分钟内，请勿多次获取动态码！';
            }
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_type'] = $log_type;
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if($state == 'true' && !empty($sms_log) && ($sms_log['add_time'] > TIMESTAMP-600)) {//同一手机号十分钟内只能发一条短信
                $state = '同一手机号十分钟内，请勿多次获取动态码！';
            }
            $time24 = TIMESTAMP-60*60*24;
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['add_time'] = array('egt',$time24);
            $num = $model_sms_log->getSmsCount($condition);
            if($state == 'true' && $num >= 5) {//同一手机号24小时内只能发5条短信
                $state = '同一手机号24小时内，请勿多次获取动态码！';
            }
            $condition = array();
            $condition['log_ip'] = getIp();
            $condition['add_time'] = array('egt',$time24);
            $num = $model_sms_log->getSmsCount($condition);
            if($state == 'true' && $num >= 20) {//同一IP24小时内只能发20条短信
                $state = '同一IP24小时内，请勿多次获取动态码！';
            }
            if($state == 'true') {
                $log_array = array();
                $model_member = Model('member');
                $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));
                $captcha = rand(100000, 999999);
                $log_msg = '【'.C('site_name').'】您于'.date("Y-m-d");
                switch ($log_type) {
                    case '1':
                        if(C('sms_register') != 1) {
                            $state = '系统没有开启手机注册功能';
                        }
                        if(!empty($member)) {//检查手机号是否已被注册
                            $state = '当前手机号已被注册，请更换其他号码。';
                        }
                        $log_msg .= '申请注册会员，动态码：'.$captcha.'。';
                        break;
                    case '2':
                        if(C('sms_login') != 1) {
                            $state = '系统没有开启手机登录功能';
                        }
                        if(empty($member)) {//检查手机号是否已绑定会员
                            $state = '当前手机号未注册，请检查号码是否正确。';
                        }
                        $log_msg .= '申请登录，动态码：'.$captcha.'。';
                        $log_array['member_id'] = $member['member_id'];
                        $log_array['member_name'] = $member['member_name'];
                        break;
                    case '3':
                        if(C('sms_password') != 1) {
                            $state = '系统没有开启手机找回密码功能';
                        }
                        if(empty($member)) {//检查手机号是否已绑定会员
                            $state = '当前手机号未注册，请检查号码是否正确。';
                        }
                        $log_msg .= '申请重置登录密码，动态码：'.$captcha.'。';
                        $log_array['member_id'] = $member['member_id'];
                        $log_array['member_name'] = $member['member_name'];
                        break;
                    default:
                        $state = '参数错误';
                        break;
                }
                if($state == 'true'){
                    $sms = new Sms();
                    $result = $sms->send($phone,$log_msg);
                    if($result){
                        $log_array['log_phone'] = $phone;
                        $log_array['log_captcha'] = $captcha;
                        $log_array['log_ip'] = getIp();
                        $log_array['log_msg'] = $log_msg;
                        $log_array['log_type'] = $log_type;
                        $log_array['add_time'] = time();
                        $model_sms_log->addSms($log_array);
                    } else {
                        $state = '手机短信发送失败';
                    }
                }
            }
        } else {
            $state = '验证码错误';
        }
       exit($state);
    }

     public function loginOp(){
     	$state='登录失败';
        if (true){
            if(C('sms_login') != 1) {
                 $state = '系统没有开启手机登录功能' ;
            }
            $phone = $_POST['phone'];
     
            $captcha = $_POST['sms_captcha'];
            $condition = array();
            $condition['log_phone'] = $phone;
            $condition['log_captcha'] = $captcha;
            $condition['log_type'] = 2;
            //debug
            // $state = $condition['log_phone'];
            $model_sms_log = Model('sms_log');
            $sms_log = $model_sms_log->getSmsInfo($condition);
            if(empty($sms_log) || ($sms_log['add_time'] < TIMESTAMP-1800)) {//半小时内进行验证为有效
                // $state = '动态码错误或已过期，重新输入';
            }
            $model_member = Model('member');
            $member = $model_member->getMemberInfo(array('member_mobile'=> $phone));//检查手机号是否已被注册
            if(!empty($member)) {//如果数据库里面有这个人那么我们就生成token 然后再setcookie,
            	$token = $this->_get_token($member['member_id'], $member['member_name'], $_POST['client']);
            	setcookie('username',$member_info['member_name'],time()+3600,'/');
                setcookie('key',$token,time()+3600,'/');
                //返回true证明登录成功
                $state = 'true';
            }
        }
        exit($state);
    }

     private function _get_token($member_id, $member_name, $client) {
        $model_mb_user_token = Model('mb_user_token');

        //重新登录后以前的令牌失效
        //暂时停用
        //$condition = array();
        //$condition['member_id'] = $member_id;
        //$condition['client_type'] = $client;
        //$model_mb_user_token->delMbUserToken($condition);
        //生成新的token
        $mb_user_token_info = array();
        $token = md5($member_name . strval(TIMESTAMP) . strval(rand(0,999999)));
        $mb_user_token_info['member_id'] = $member_id;
        $mb_user_token_info['member_name'] = $member_name;
        $mb_user_token_info['token'] = $token;
        $mb_user_token_info['login_time'] = TIMESTAMP;
        $mb_user_token_info['client_type'] = $client;

        $result = $model_mb_user_token->addMbUserToken($mb_user_token_info);

        if($result) {
            return $token;
        } else {
            return null;
        }

    }


}