<?php
namespace app\admin\controller;

use think\Request;
use Think\Db;

class Member extends BaseAdmin
{
    public function lister()
    {

        $map=[];

        $title = input('title');

        $map['phone|nickname'] = array('like','%'.$title.'%');

        $this->assign('title',$title);

        $list=db("user")->where($map)->where("type",1)->order("uid desc")->paginate(10,false,['query'=>request()->param()]);
        
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);   
        return $this->fetch();
    }
    public function index()
    {
        $map=[];

        $title = input('title');

        $map['phone|nickname'] = array('like','%'.$title.'%');

        $this->assign('title',$title);

        $list=db("user")->where($map)->where("type",2)->order("uid desc")->paginate(10,false,['query'=>request()->param()]);

        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);
        return $this->fetch();
    }
    public function add()
    {
        return $this->fetch();
    }
    public function adds()
    {
        return $this->fetch();
    }
    public function saves()
    {
        $data=input("post.");

        $status=input("status");

        if($status){
            $data['status']=1;
        }else{
            $data['status']=2;
        }

        $uid=input("uid");

        if(!$uid){

            $data['type']=2;

            $data['time']=time();

            $re=\db("user")->where("phone",$data['phone'])->find();

            if($re){
                $this->error("此手机号码已注册");exit();
            }

            $rea=\db("user")->insert($data);

            if($rea){
                $this->success("保存成功",url("index"));
            }else{
                $this->error("系统繁忙，请稍后再试");
            }

        }else{

            $res=\db("user")->where("uid",$uid)->update($data);

            if($res){
                $this->success("保存成功",url("index"));
            }else{
                $this->error("系统繁忙，请稍后再试");
            }

        }


    }
    /**
     *保存用户
     */
    public function save()
    {
        $data=input("post.");

        $status=input("status");

        if($status){
            $data['status']=1;
        }else{
            $data['status']=2;
        }

        $uid=input("uid");

        if(!$uid){

            $data['type']=1;

            $data['time']=time();

            $re=\db("user")->where("phone",$data['phone'])->find();

            if($re){
                $this->error("此手机号码已注册");exit();
            }

            $rea=\db("user")->insert($data);

            if($rea){
                $this->success("保存成功",url("lister"));
            }else{
                $this->error("系统繁忙，请稍后再试");
            }

        }else{

            $res=\db("user")->where("uid",$uid)->update($data);

            if($res){
                $this->success("保存成功",url("lister"));
            }else{
                $this->error("系统繁忙，请稍后再试");
            }

        }


    }
    public function modifys()
    {
        $uid=input("uid");

        $re=\db("user")->where("uid",$uid)->find();

        $this->assign("re",$re);

        return $this->fetch();
    }
    public function modify()
    {
        $uid=input("uid");

        $re=\db("user")->where("uid",$uid)->find();

        $this->assign("re",$re);

        return $this->fetch();
    }



    public function change_money(){
        $uid=input("uid");

        $type=input("type");

        $money=input("money");

        $content=input("content");

        $user=db("user")->where("uid",$uid)->find();

        if(!$user){
            $this->error("非法操作");exit();
        }

        if($type == 1){
            $data['uid']=$uid;
            $data['money']=abs($money);
            $data['type']=1;
            $data['oper']=db("admin")->where("id",session('uid'))->find()['username'];
            $data['content']=$content;
            $data['time']=time();

            $res = db('user')->where('uid', $uid)->setInc('money', $money);
            db('user')->where('uid', $uid)->setInc('recharge_money', $money);
            if($res){
                db("money_log")->insert($data);
                $this->success("操作成功");
            }else{
                $this->error("操作失败");
            }

        }else{

            $datas['uid']=$uid;
            $datas['money']=abs($money);
            $datas['type']=2;
            $datas['oper']=db("admin")->where("id",session('uid'))->find()['username'];
            $datas['content']=$content;
            $datas['time']=time();

            $res = db('user')->where('uid', $uid)->setDec('money', $money);
            db('user')->where('uid', $uid)->setDec('recharge_money', $money);
            if($res){
                db("money_log")->insert($datas);
                $this->success("操作成功");
            }else{
                $this->error("操作失败");
            }
        }
        

    }

    /**
    * 删除
    *
    * @return void
    */
    public function delete()
    {
        $id=input('id');
        $re=db("user")->where("uid=$id")->find();
        if($re){
            
            $del=db("user")->where("uid=$id")->setField("is_delete",1);
            if($del){
                
                echo '0';
            }else{
                echo '1';
            }
        }else{
            echo '2';
        }
    }
    /**
     * 运营列表
     */
    public function operate()
    {

        $map=[];

        $title=input("title");

        if($title){
            $map['nickname']=["like","%".$title."%"];
        }else{
            $title='';
        }
        $this->assign("title",$title);
        
        $list=db("user")->where(["type"=>1,"is_delete"=>0])->order("uid desc")->paginate(20)->each(function($v,$k){
            $v['agent_num']=db("user")->where(["type"=>2,"fid"=>$v['uid'],"is_delete"=>0])->count();
            $v['custom_num']=db("user")->where(["type"=>3,"fids"=>$v['uid'],"is_delete"=>0])->count();

            return $v;

        });

        $this->assign("list",$list);

        $page=$list->render();

        $this->assign("page",$page);

        return $this->fetch();
    }
    public function addo()
    {
        return $this->fetch();
    }
    public function saveo()
    {
        
        $username=input("username");

        $user=db("user")->where(["username"=>$username,"is_delete"=>0])->find();

        if($user){
            $this->error("此账号已存在");
        }else{

            $data=input('post.');
            $data['time']=time();
    
            $re=db("user")->insert($data);
    
            if($re){
                $this->success("保存成功",url("Member/operate"));
            }else{
                $this->error("保存失败");
            }
        }
        
    }
    /**
     * 编辑
     */
    public function modifyo()
    {
        $uid=input("uid");

        $re=db("user")->where(["uid"=>$uid,"is_delete"=>0])->find();

        $this->assign("re",$re);
        
        return $this->fetch(); 
    }

    public function usaveo()
    {
        
        

        $uid=input("uid");

        $re=db("user")->where(["uid"=>$uid,"is_delete"=>0])->find();

        if($re){

            $username=input("username");

            $user=db("user")->where(["username"=>$username,"uid"=>['neq',$uid]])->find();

            if($user){
                $this->error("此账号已存在");
            }else{

                $data=input('post.');
            

                $re=db("user")->where("uid",$uid)->update($data);

                if($re){
                    $this->success("修改成功",url("Member/operate"));
                }else{
                    $this->error("修改失败");
                }
            }

        }else{

            $this->error("参数错误");

        }

        
    }
   
   

















 
}