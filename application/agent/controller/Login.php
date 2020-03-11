<?php

namespace app\agent\controller;


use think\Request;

class Login extends Common
{
    public function  index(){
        return view('index');
    }
    public function check(){
       // $data = input('post.');
        if(!captcha_check(input('post.verify'))) {
            // 校验失败
            $this->error('验证码不正确');exit;
        }
        $unm=input('post.username');
        $pwd=input('post.password');
        $re=db("user")->where(['phone'=>$unm,'pwd'=>$pwd,'is_delete'=>0,'type'=>2])->find();

        if($re){
            session('aid',$re['uid']);
            \session('username',$re['username']);
            $time = date('Y-m-d H:i:s',time());
            $re_pre = db("user")->where("uid",$re['uid'])->find();
            $pretime = $re_pre['curtime'];
            $ip=Request::instance()->ip();
            $data['pretime']=$pretime;
            $data['curtime']=$time;
            $data['ip']=$ip;
            $res = db("user")->where("uid",$re['uid'])->update($data);
            
            //增加操作日志
            $arr=array();
            $arr['type']="后台登录";
            $arr['time']=date('Y-m-d H:i:s',time());
            $arr['admin']=$re['username'];
            $arr['ip']=$ip;
            $this->logs->add_logs($arr);
            
            $this->success('登陆成功 ^_^',url('Index/index'));
        }else{
            $this->error('登录失败：用户名或密码错误。',url('Login/index'));
        }
    }
    public function logout(){
        session("aid",null);
        $this->redirect('Login/index');
    }
    function modify(){
        if (! defined('CONTROLLER_NAME')) {
            define('CONTROLLER_NAME', $this->request->controller());
        }
        if (! defined('ACTION_NAME')) {
            define('ACTION_NAME', $this->request->action());
        }
        $id=\session('aid');
        $re=db("user")->where("uid=$id")->find();
        $this->assign("re",$re);
        return view('modify');
    }
    function save(){
        $ob=db("user");
        $old_pwd=input('old_pwd');
        $id=input('id');
        $re=$ob->where("uid=$id and pwd='$old_pwd'")->find();
        if($re){
            $data['pwd']=input('pwd');
            $res=$ob->where("uid=$id")->update($data);
            if($res){
                $this->success("修改成功！");
            }else{
                $this->error("修改失败！");
            }
        }else{
            $this->error("原密码错误！");
        }

        
    }
 


}