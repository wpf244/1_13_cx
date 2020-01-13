<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class BaseUser extends Controller
{
    function _initialize(){
        if (!defined('CONTROLLER_NAME')) {
            define('CONTROLLER_NAME', $this->request->controller());
        }
        if (!defined('ACTION_NAME')) {
            define('ACTION_NAME', $this->request->action());
        }
      
        $sys=db('sys')->where("id=1")->find();
        $this->assign("sys",$sys);

        $seo=db('seo')->where("id=1")->find();
        $this->assign("seo",$seo);

        if(empty(session("userid"))){
            $this->redirect("Login/login");
        }else{
            $userid=session("userid");
            $user=db("user")->where("uid",$userid)->find();
            if(empty($user)){
                session("userid",null);
                $this->redirect("Login/login");
            }
        }



    
    }
}