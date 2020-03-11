<?php

namespace app\agent\controller;

use think\Controller;
use app\agent\model\Sever;

class BaseAdmin extends Controller{
    function _initialize(){
        if (!defined('CONTROLLER_NAME')) {
            define('CONTROLLER_NAME', $this->request->controller());
        }
        if (!defined('ACTION_NAME')) {
            define('ACTION_NAME', $this->request->action());
        }
        if(empty(session('aid'))){
            $this->redirect("login/index");
        }
        $sys=db('sys')->where("id=1")->find();
        $this->assign("sys",$sys);
        
        $aid=session('aid');
        $admin=db('user')->where("uid=$aid")->find();
        $this->assign("admin",$admin);

        
        $this->logs=new Sever();
        

        
    }
}