<?php session_start();
/**
 * 前台登录 退出操作
 *
 *
 *
 *
 * @copyright  Copyright (c) 2007-2015 ShopNC Inc. (http://www.shopnc.net)
 * @license    http://www.shopnc.net
 * @link       http://www.shopnc.net
 * @since      File available since Release v1.1
 */

use Shopnc\Tpl;

defined('InShopNC') or exit('Access Invalid!');

class loginControl extends mobileHomeControl {

    public function __construct(){
        parent::__construct();
    }

    /**
     * 登录
     */
    public function indexOp(){
        if(empty($_POST['username']) || empty($_POST['password']) || !in_array($_POST['client'], $this->client_type_array)) {
            output_error('登录失败');
        }

        $model_member = Model('member');

        $array = array();
        $array['member_name']   = $_POST['username'];
        $array['member_passwd'] = md5($_POST['password']);
        $member_info = $model_member->getMemberInfo($array);
        if(empty($member_info) && preg_match('/^0?(13|15|17|18|14)[0-9]{9}$/i', $_POST['username'])) {//根据会员名没找到时查手机号
            $array = array();
            $array['member_mobile']   = $_POST['username'];
            $array['member_passwd'] = md5($_POST['password']);
            $member_info = $model_member->getMemberInfo($array);
        }
        if(empty($member_info) && (strpos($_POST['username'], '@') > 0)) {//按邮箱和密码查询会员
            $array = array();
            $array['member_email']   = $_POST['username'];
            $array['member_passwd'] = md5($_POST['password']);
            $member_info = $model_member->getMemberInfo($array);
        }

        if(!empty($member_info)) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('登录失败');
            }
        } else {
            output_error('用户名密码错误');
        }
    }

    /**
     * 登录生成token
     */
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
    public function qqloginOp(){
       $model_member   = Model('member');
       // echo $model_member
       $user_array = array();
       $array['member_qqopenid']   = $_SESSION['openid'];

       // $_SESSION['openid']=123;
       //DEBUG
       // require_once('HttpRequest');
       // echo 'appid:'.$_SESSION["appid"].'appkey'. $_SESSION["appkey"].'token'.$_SESSION["token"].'secret'.$_SESSION["secret"]. 'openid'.$_SESSION["openid"];
       $member_info = $model_member->getMemberInfo($array);

       if (is_array($member_info) && count($member_info)>0){
          $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], 'wap');
          if($token) {
            //output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
        
            setcookie('username',$member_info['member_name'],time()+3600,'/');
            setcookie('key',$token,time()+3600,'/');

            @header('Location: '.WAP_SITE_URL.'/tmpl/member/member.html?act=member');
            return;
        } 
        else {
            output_error('error');
        }
    }

          // $model_member->checkloginMember();
            //获取qq账号信息
            require_once (BASE_PATH.'/api/qq/user/get_user_info.php');
            $qquser_info = get_user_info($_SESSION["appid"], $_SESSION["appkey"], $_SESSION["token"], $_SESSION["secret"], $_SESSION["openid"]);
            Tpl::output('qquser_info',$qquser_info);

            //处理qq账号信息
            $qquser_info['nickname'] = trim($qquser_info['nickname']);
            $user_passwd = rand(100000, 999999);
            /**
             * 会员添加
             */
            $user_array = array();
            $user_array['member_name']      = $qquser_info['nickname'];
            $user_array['member_passwd']    = $user_passwd;
            $user_array['member_email']     = '';
            $user_array['member_qqopenid']  = $_SESSION['openid'];//qq openid
            $user_array['member_qqinfo']    = serialize($qquser_info);//qq 信息
            $rand = rand(100, 899);
            if(strlen($user_array['member_name']) < 3) $user_array['member_name']       = $qquser_info['nickname'].$rand;
            $check_member_name  = $model_member->getMemberInfo(array('member_name'=>trim($user_array['member_name'])));
            $result = 0;
            if(empty($check_member_name)) {
                $result = $model_member->addMember($user_array);
            }else {
                for ($i = 1;$i < 999;$i++) {
                    $rand += $i;
                    $user_array['member_name'] = trim($qquser_info['nickname']).$rand;
                    $check_member_name  = $model_member->getMemberInfo(array('member_name'=>trim($user_array['member_name'])));
                    if(empty($check_member_name)) {
                        $result = $model_member->addMember($user_array);
                        break;
                    }
                }
            }
             if($result) {
                
                $avatar = @copy($qquser_info['figureurl_qq_2'],BASE_UPLOAD_PATH.'/'.ATTACH_AVATAR."/avatar_$result.jpg");
                $update_info    = array();
                if($avatar) {
                    $update_info['member_avatar']   = "avatar_$result.jpg";
                    $model_member->editMember(array('member_id'=>$result),$update_info);
                }
                $member_info = $model_member->getMemberInfo(array('member_name'=>$user_array['member_name']));
                $model_member->createSession($member_info,true);
                $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], 'wap');
                 setcookie('username',$member_info['member_name'],time()+3600,'/');
                 setcookie('key',$token,time()+3600,'/');    
              @header('Location: '.WAP_SITE_URL.'/tmpl/member/member.html?act=member');
                return;
               
            
        }
    }

    /**
     * 注册
     */
    public function registerOp(){
        $model_member   = Model('member');

        $register_info = array();
        $register_info['username'] = $_POST['username'];
        $register_info['password'] = $_POST['password'];
        $register_info['password_confirm'] = $_POST['password_confirm'];
        $register_info['email'] = $_POST['email'];
        $member_info = $model_member->register($register_info);
        if(!isset($member_info['error'])) {
            $token = $this->_get_token($member_info['member_id'], $member_info['member_name'], $_POST['client']);
            if($token) {
                output_data(array('username' => $member_info['member_name'], 'userid' => $member_info['member_id'], 'key' => $token));
            } else {
                output_error('注册失败');
            }
        } else {
            output_error($member_info['error']);
        }

    }
}
