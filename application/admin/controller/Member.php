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
     * 股权日志
     *
     * @return void
     */
    public function money_log(){
        $id = Request::instance()->param('uid', 0);
        $user = db('user')->where('uid', $id)->find();
        $list = db("money_log")->where('uid', $id)->paginate(10);
        $this->assign('list', $list);
        $this->assign('user', $user);
        return $this->fetch();
    }

    /**
     * 奖励金日志
     *
     * @return void
     */
    public function bonus_log(){
        $id = Request::instance()->param('id', 0);
        $user = db('user')->where('uid', $id)->find();
        $list = db("bonus_log")->where('u_id', $id)->paginate(10);
        $this->assign('list', $list);
        $this->assign('user', $user);
        return $this->fetch();
    }

    /**
     * 奖励金提现
     *
     * @return void
     */
    public function balance(){
        $wx_account = Request::instance()->param('wx_account', '');
        $wx_nickname = Request::instance()->param('wx_nickname', '');
        $status = Request::instance()->param('status', '-1');
        $start = Request::instance()->param('start', '');
        $end = Request::instance()->param('end', '');
        $map = [];
        if($status != -1){
            $map['status'] = $status;
        }
        if($wx_account != ''){
            $map['wx_account'] = array('like', '%'.$wx_account.'%');
        }
        if($wx_nickname != ''){
            $map['wx_nickname'] = array('like', '%'.$wx_nickname.'%');
        }
        if($start != '' && $end != ''){
            $map['c.time'] = array(array('egt',strtotime($start)),array('elt',strtotime($end.' 23:55:55')),'AND');
        }elseif($start == '' && $end != ''){
            $map['c.time'] = array('elt',strtotime($end.' 23:55:55'));
        }elseif($start != '' && $end == ''){
            $map['c.time'] = array('egt',strtotime($start));
        }
        $list = db("bonus_withdrow")->alias('c')
        ->field('u.nickname, u.image, c.id, c.uid, c.money, c.wx_nickname, c.wx_account, c.status, c.time')
        ->join('user u','u.uid=c.uid')->where($map)->order('c.time desc')->paginate(10,false,['query'=>request()->param()]);

        $this->assign('wx_account', $wx_account);
        $this->assign('wx_nickname', $wx_nickname);
        $this->assign('status', $status);
        $this->assign('start', $start);
        $this->assign('end', $end);
        $this->assign('list', $list);
        return $this->fetch('balance');
    }

    /**
     * 奖励金状态
     *
     * @return void
     */
    public function balance_status(){
        $id = Request::instance()->param('id');
        $ftype = Request::instance()->param('ftype');
        db("bonus_withdrow")->where('id', $id)->setField('status', $ftype);
        $user = db("bonus_withdrow")->where('id', $id)->find();
        if($ftype == 3){
            //驳回，返回余额
            db("user")->where('uid', $user['uid'])->setInc('bonus', $user['money']);
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
            
            $del=db("user")->where("uid=$id")->delete();
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
    * 升级申请列表
    *
    * @return void
    */
    public function apply()
    {
        $status=input("status");
        if($status){
            $map['u_status']=['eq',$status];
        }else{
            $status=0;
        }
        $map['type']=['eq',0];
        $list=db("user_apply")->alias("a")->field("a.*,b.nickname")->where($map)->join("user b","a.u_id=b.uid")->order("id desc")->paginate(10);
        $this->assign("list",$list);

        $page=$list->render();
        $this->assign("page",$page);

        $this->assign("status",$status);

        return $this->fetch();
    }
    /**
    * 通过审核
    *
    * @return void
    */
    public function change()
    {
        $id=input("id");
        $re=db("user_apply")->where("id",$id)->find();
        if($re){ 
           if($re['u_status'] == 0){
            $uid=$re['u_id'];
            $level=$re['u_level'];
            // 启动事务
                Db::startTrans();
                try{
                   db("user_apply")->where("id",$id)->setField("u_status",1);
                   db("user")->where("uid",$uid)->setField("level",$level);
                    // 提交事务
                    Db::commit();   
                   
                } catch (\Exception $e) {
                    // 回滚事务
                    Db::rollback();
                    $this->redirect('apply');
                }
           
                $this->redirect('apply');
            
           }else{
            $this->redirect('apply');
           }
        }else{ 
            $this->redirect('apply');
        }
    }
    /**
    * 驳回
    *
    * @return void
    */
    public function bo()
    {
        $id=input("id");
        $re=db("user_apply")->where("id",$id)->find();
        if($re){ 
           if($re['u_status'] == 0){
          
            db("user_apply")->where("id",$id)->setField("u_status",2);
             
            $this->redirect('apply');
            
           }else{
            $this->redirect('apply');
           }
        }else{ 
            $this->redirect('apply');
        }
    }
    /**
    * 入住酒店申请列表
    *
    * @return void
    */
    public function hotel_apply()
    {
        $status=input("status");
        if($status){
            $map['u_status']=['eq',$status];
        }else{
            $status=0;
        }
        $map['type']=['eq',1];
        $list=db("user_apply")->alias("a")->field("a.*,b.nickname")->where($map)->join("user b","a.u_id=b.uid")->order("id desc")->paginate(10);
        $this->assign("list",$list);

        $page=$list->render();
        $this->assign("page",$page);

        $this->assign("status",$status);

        return $this->fetch();
    }
    /**
    * 通过审核
    *
    * @return void
    */
    public function changes()
    {
        $id=input("id");
        $re=db("user")->where("uid",$id)->find();
        if($re){ 
           if($re['status'] == 1){


               db("user")->where("uid",$id)->setField("status",2);

                echo '1';
            
           }else{
               db("user")->where("uid",$id)->setField("status",1);

               echo '1';
           }
        }else{ 
            echo '2';
        }
    }
     /**
    * 驳回
    *
    * @return void
    */
    public function bos()
    {
        $id=input("id");
        $re=db("user_apply")->where("id",$id)->find();
        if($re){ 
           if($re['u_status'] == 0){
               $data['u_status']=2;
               $data['rebut']=input("rebut");
          
            db("user_apply")->where("id",$id)->update($data);
             
            echo '1';
            
           }else{
            echo '0';
           }
        }else{ 
            echo '2';
        }
    }
   
   

















 
}