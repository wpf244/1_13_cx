<?php
namespace app\index\controller;

use think\Request;

class Login extends BaseHome
{
    public function login()
    {
        $lb=db("lb")->where("fid",31)->find();

        $this->assign("lb",$lb);
        
        return $this->fetch();
    }
    public function register()
    {
        $uid=input("uid");

        if(empty($uid)){
            $uid=0;
        }

        $this->assign("uid",$uid);
        
        return $this->fetch();
    }
 
    public function send_code()
    {
        $phone=input('phone');
        $re=db('user')->where("phone",$phone)->find();
        if($re){
            echo '0';
        }else{
            $code =mt_rand(100000,999999);       
            $data['phone']=$phone;
            $data['code']=$code;
            $data['time']=time();
            $re=\db("sms_code")->where("phone",$phone)->find();
            if($re){
                $del=db("sms_code")->where("phone",$phone)->delete();
            }
            $rea=db("sms_code")->insert($data);
            Post($phone,$code);
            echo '1';
           
        }
    }
    public function save()
    {
        $phone=input("phone");
        $reu=db("user")->where("phone",$phone)->find();
        $code=input("code");
        $re=db("sms_code")->where(['phone'=>$phone,'code'=>$code])->find();

        $uid=input("uid");

        if($re){
            $time=$re['time'];
            $times=time();
            $c_time=($times-$time);
            if($c_time < 300){
                db("sms_code")->where("id",$re['id'])->delete();
                if($reu){
                    $this->error("此手机号码已注册",url('Login/login'));
                }else{
                  $data['phone']=input("phone");
                  $data['pwd']=input("pwd");
                  $data['time']=time();

                  if($uid == 0){
                    $rea=db("user")->insert($data);

                    $uid=db("user")->getLastInsID();
                  }else{
                      $rea=db("user")->where("uid",$uid)->update($data);
                  }
               
                  if($rea){
                       session("userid",$uid);
                       $this->success("注册成功",url('User/index')); 
                  }else{
                    $this->error("系统繁忙,请稍后再试",url('Login/login'));
                  }
                                  
                }
            }else{
                $this->error("验证码已失效");
            }
        }else{
            $this->error("验证码错误");
        }
    }
    public function out()
    {
        session("userid",null);
        $this->redirect('Login/login');
    }
    public function forget()
    {
        return $this->fetch();
    }
    public function send_codes()
    {
        $phone=input('phone');
        $re=db('user')->where("phone",$phone)->find();
        if($re){
            $code =mt_rand(100000,999999);       
            $data['phone']=$phone;
            $data['code']=$code;
            $data['time']=time();
            $re=\db("sms_code")->where("phone",$phone)->find();
            if($re){
                $del=db("sms_code")->where("phone",$phone)->delete();
            }
            $rea=db("sms_code")->insert($data);
            Post($phone,$code);
            echo '1';
        }else{
            
            echo '0';
           
        }
    }
    public function usave()
    {
        $phone=input("phone");
        $reu=db("user")->where("phone",$phone)->find();
        $code=input("code");
        $re=db("sms_code")->where(['phone'=>$phone,'code'=>$code])->find();
        if($re){
            $time=$re['time'];
            $times=time();
            $c_time=($times-$time);
            if($c_time < 300){
                db("sms_code")->where("id",$re['id'])->delete();
                if($reu){
                    $data['pwd']=input("pwd");
                  
                    $rea=db("user")->where("uid",$reu['uid'])->update($data);
  
                    if($rea){
                        
                         $this->success("修改成功",url('Login/Login')); 
                    }else{

                        $this->error("新密码与原密码相同",url('Login/login'));

                    }
                    
                }else{
                 
                  $this->error("此手机号码已注册",url('Login/login'));
                              
                }
            }else{
                $this->error("验证码已失效");
            }
        }else{
            $this->error("验证码错误");
        }
    }
      /**
    * 登录
    *
    * @return void
    */
    public function check()
    {
        $phone=input("phone");
        $pwd=input("pwd");
        $re=db("user")->where(["phone"=>$phone,"pwd"=>$pwd])->find();
        if($re){

            if($re['status'] == 1){
                session("userid",$re['uid']);
                $this->success("登录成功",url('Index/index'));
            }else{
                $this->error("此账号未启用，请联系网站管理员",url('Login/login'));
            }

        }else{
            $this->error("账号或密码错误",url('Login/login')); 
        }
       
    }
    /**
    * 微信授权登录
    *
    * @return void
    */
    public function wxlogin()
    {
        
        $pay=db("payment")->where("id",1)->find();

        $appid=$pay['appid'];

        $appsecret=$pay['appsecret'];

        $url=Request::instance()->url(true);

        $urlcode="https://open.weixin.qq.com/connect/oauth2/authorize?appid=$appid&redirect_uri=$url&response_type=code&scope=snsapi_userinfo&state=1#wechat_redirect";

		if(empty(input("code"))){
			header("location:".$urlcode);
		}

		$code=input("code");
	
		$url="https://api.weixin.qq.com/sns/oauth2/access_token?appid=$appid&secret=$appsecret&code=".$code."&grant_type=authorization_code";

		$result=json_decode(file_get_contents($url),true);
		if(empty($result['access_token'])){
			echo '失败:access_token';exit;
		}
		$token=$result['access_token'];
		$openid=$result['openid'];
		$url="https://api.weixin.qq.com/sns/userinfo?access_token=".$token."&openid=".$openid."&lang=zh_CN";
		$result=json_decode(file_get_contents($url),true);
		//   	    var_dump($result);exit;
		if(empty($result['openid'])){
			echo '失败:openid';
			exit;
        }
        
        $re=db("user")->where("openid",$openid)->find();

        if($re){

            if($re['phone']){
                session("userid",$re['uid']);
                $this->redirect("User/index");
            }else{
                $this->redirect("Login/register",array('uid'=>$re['uid']));
            }

        }else{
            $data['openid']=$openid;
            $data['nickname']=$result['nickname'];
            $data['image']=$result['headimgurl'];
            $data['time']=time();

            db("user")->insert($data);

            $uid=db("user")->getLastInsID();

            $this->redirect("Login/register",array('uid'=>$uid));
             
        }
    }
   
    


}