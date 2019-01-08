<?php
namespace app\wechat\controller;

use think\request;
use think\Controller;
use think\Loader;
use think\Config;
use think\Db;
class Wechat extends Controller {
    public $appid,$secret;

    /*微信验证*/
    public function wx()
    {
        define("TOKEN", "pondbay");
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
        $this->responseMsg();
    }
    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];
        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );
        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }
    /*微信toke*/
    public function wxtoke()
    {
        $appid = $this->appid=Config::get('APPID');
        $secret = $this->secret=Config::get('APPSECRET');
        $token  = cache('Token');

        if(!$token['access_token'] || $token['expires_in'] <= time()){
            $rslt  = gets("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}");
            if($rslt){
                $accessArr = array(
                    'access_token'=>$rslt['access_token'],
                    'expires_in'=>time()+$rslt['expires_in']-200
                );
                cache('Token',$accessArr) ;
                $token = $rslt;
            }
        }
        return $token;
    }
    /*微信菜单*/
    public function menu()
    {
        $token = $this->wxtoke();
        $appid = $this->appid;
        $secret = $this->secret;
        $rslt  = gets("https://api.weixin.qq.com/cgi-bin/menu/delete?access_token=".$token['access_token']) ; ##删除菜单
        $menu = array(
            'button'=>array(
                array(
                    "type"=>"view",
                    "name"=>"Call Me",
                    "url"=>"http://www.scptkj.com/index.html"
                ),
                array(
                    "type"=>"view",
                    "name"=>"小红帽",
                    "url"=>"http://www.scptkj.com/insidelog"
                ),
            )
        );
        $rslt  = posts("https://api.weixin.qq.com/cgi-bin/menu/create?access_token=".$token['access_token'],json_encode($menu,JSON_UNESCAPED_UNICODE)) ; ##JSON_UNESCAPED_UNICODE  防止json-encode中文转码

    }
    /*获取用户信息插入数据库*/
    public function adduser()
    {

        $appid = $this->appid=Config::get('APPID');
        $secret = $this->secret=Config::get('APPSECRET');
        $code = $_GET['code'] ;

        ##获取网页授权的access_token 和openid
        $rslt  = gets("https://api.weixin.qq.com/sns/oauth2/access_token?appid={$this->appid}&secret={$this->secret}&code={$code}&grant_type=authorization_code");

        ##通过access_token 和openid获取获取信息
        $user  = gets("https://api.weixin.qq.com/sns/userinfo?access_token=".$rslt['access_token']."&openid=".$rslt['openid']."&lang=zh_CN ");
        $data = array();
        $data['openid'] = $user['openid'];
        $data['nickname'] = htmlspecialchars($user['nickname']);
        $data['sex'] = $user['sex'];
        $data['city'] = $user['city'];
        $data['province'] = $user['province'];
        $data['headimgurl'] = $user['headimgurl'];
        $res = Db::name('wechat_user')->where(['openid'=>$user['openid']])->find();
        if(empty($res)){
            $insert_user = Db::name('wechat_user')->insert($data);
            if($insert_user){
                session('MEMBER',$res);
                $this->redirect('Index/index/index');
            }else{
                $this->error('添加失败');
            } 
             
        }else{
            session('MEMBER',$res);
            
            // pr($res['id']);die;
            $update_user = Db::name('wechat_user')->where(['id'=>$res['id']])->update($data);
            $update_user==0?$this->redirect('Index/index/index'):$this->redirect('Index/index/index');
            // ?$this->redirect('Index/index/index'):$this->error('更新失败');
        }
        
      

    }

    public function firstValid(){
        //检验签名的合法性
        if($this->_checkSignature()){
            //签名合法，告知微信公众平台服务器
            echo $_GET['echostr'];
        }
    }


    private function _checkSignature(){
        //获得由微信公众平台请求的验证数据
        $signature = $_GET['signature'];
        $timestamp = $_GET['timestamp'];
        $nonce = $_GET['nonce'];
        //将时间戳，随机字符串，token按照字母顺序排序，病并连接
        $tmp_arr = array($this->_token,$timestamp,$nonce);
        sort($tmp_arr,SORT_STRING);//字典顺序
        $tmp_str = implode($tmp_arr);//连接
        $tmp_str = sha1($tmp_str);//sha1加密
        if($signature==$tmp_str){
            return true;
        }else{
            return false;
        }

    }
    private function is_utf8($str)//判断是否是utf8编码
    {
        returnpreg_match('//u', $str);
    }
 
    //群发消息

    //单发消息
    public function send(){
        
        $this->wxtoke();
          
        $test = new \SendAllMsg($this->appid,$this->secret);
      
        $test->sendMsgToall();
    }
        

}
