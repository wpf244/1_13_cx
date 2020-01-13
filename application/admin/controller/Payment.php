<?php
namespace app\admin\controller;

class Payment extends BaseAdmin
{
    public function wxpay()
    {
        $re=db("payment")->where("id",1)->find();
        $this->assign("re",$re);
        return $this->fetch();
    }
    public function save()
    {
        $data=input("post.");
        $res=db("payment")->where("id",1)->update($data);
        if($res){
            $this->success("修改成功");
        }else{
            $this->error("修改失败");
        }
    }
}