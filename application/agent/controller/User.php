<?php
namespace app\agent\controller;

class User extends BaseAdmin
{
    public function index()
    {
        $map=[];

        $title = input('title');

        $map['phone|nickname'] = array('like','%'.$title.'%');

        $this->assign('title',$title);

        $aid=session("aid");

        $list=db("user")->where($map)->where(["type"=>3,"is_delete"=>0,"fid"=>$aid])->order("uid desc")->paginate(10,false,['query'=>request()->param()]);
        
        $this->assign("list",$list);

        $page=$list->render();

        $this->assign("page",$page);
        
        return $this->fetch();
    }
    /**
     * 添加客户
     */
    public function adds()
    {
        return $this->fetch();
    }
    /**
     *保存用户
     */
    public function saves()
    {
        $data=input("post.");
 
        $uid=input("uid");

        if(!$uid){

            $data['type']=3;

            $data['time']=time();

            $data['fid']=session("aid");

            $aid=session("aid");

            $data['fids']=db("user")->where("uid",$aid)->find()['fid'];

            $re=\db("user")->where(["phone"=>$data['phone'],"is_delete"=>0])->find();

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
     * 修改信息
     */
    public function modify()
    {
        $uid=input("uid");

        $re=\db("user")->where(["uid"=>$uid,"is_delete"=>0])->find();

        $this->assign("re",$re);

        return $this->fetch();
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
}