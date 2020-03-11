<?php

namespace app\operate\controller;

use think\Controller;
use app\admin\model\Sever;

class BaseAdmin extends Controller{
    function _initialize(){
        if (!defined('CONTROLLER_NAME')) {
            define('CONTROLLER_NAME', $this->request->controller());
        }
        if (!defined('ACTION_NAME')) {
            define('ACTION_NAME', $this->request->action());
        }
        if(empty(session('oid'))){
            $this->redirect("login/index");
        }
        $sys=db('sys')->where("id=1")->find();
        $this->assign("sys",$sys);
        
        $oid=session('oid');
        $admin=db('user')->where("uid=$oid")->find();
        $this->assign("admin",$admin);

        
        $this->logs=new Sever();
        

        
    }
}