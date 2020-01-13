<?php
namespace app\index\controller;

use think\Request;

class News extends BaseHome
{
    public function index()
    {
        //轮播图
        $lb=db("lb")->where(["fid"=>4,"status"=>1])->order(["sort asc","id desc"])->select();

        $this->assign("lb",$lb);

        $res=db("news")->where(["status"=>1])->order(["sort asc","id desc"])->select();

        $this->assign("res",$res);
        
        return $this->fetch();
    }
    public function detail()
    {
        
        $id=input("id");

        $re=db("news")->where("id",$id)->find();

        $this->assign("re",$re);

        db("news")->where("id",$id)->setInc("browse",1);

        $share_title=db("lb")->where("fid",46)->find();

        $share_title['name']=$re['title'];

        $share_title['desc']=$re['title'];

        $share_title['image']=$re['image'];

        $share_title['url']=Request::instance()->url(true);

        $share_title['urls']=Request::instance()->domain();
        
        $this->assign("share_title",$share_title);
        

        return $this->fetch();
    }

}