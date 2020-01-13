<?php
namespace app\index\controller;

class Shop extends BaseUser
{
    public function index()
    {
        $uid=session("userid");

      //  $res=db("cars")->where("uid",$uid)->select();
        
        $money=0;

        $res=db("cars")->alias("a")->field("a.*,b.xprice,b.code,b.type,b.specs
        ")->where(["uid"=>$uid])->where("up",1)->join("goods b","a.gid=b.id")->select();

        foreach($res as $k => $v){
            if($v['sid'] == 0){
                $res[$k]['price']=$v['xprice'];
            }else{
                $spec=db("goods_spec")->where(["sid"=>$v['sid'],"s_status"=>1])->find();
                if($spec){
                    $res[$k]['price']=$spec["s_xprice"];
                }else{
                    $res[$k]['price']=$v['xprice'];
                    $res[$k]['s_name']='';
                }
            }     
        }

        foreach($res as $k => $v){
            $res[$k]['prices']=sprintf("%.2f",$v['price']*$v['num']);
  
        }
        $this->assign("res",$res);
       

        

        // $this->assign("res",$res);

      //  var_dump($res);

        $list = db("cars")->alias("a")->where(["uid"=>$uid,"a.status"=>1])->where("up",1)->join("goods b","a.gid=b.id")->select();

        foreach($list as  $v){
            if($v['sid'] == 0){
                $price=$v['xprice'];
            }else{
                $spec=db("goods_spec")->where(["sid"=>$v['sid'],"s_status"=>1])->find();
                if($spec){
                    $price=$spec["s_xprice"];
                }else{
                    $price=$v['xprice'];
                }
            } 
            $money+=$price*$v['num'];    
        }

        // foreach($list as $vl){
        //     $money+=($vl['price']*$vl['num']);
        // }
        
        $money=sprintf("%.2f",$money);
    
        $this->assign("money",$money);

        if(count($res) == count($list)){
            $quan=1;
        }else{
            $quan=0;
        }
        $this->assign("quan",$quan);

        return $this->fetch();
    }
    /**
    * 修改选中状态
    *
    * @return void
    */
    public function change()
    {
        $cid=\input("cid");

        $re=db("cars")->where("cid",$cid)->find();

        if($re){
            if($re['status'] == 0){
                db("cars")->where("cid",$cid)->setField("status",1);
            }
            if($re['status'] == 1){
                db("cars")->where("cid",$cid)->setField("status",0);
            }
            echo '0';
        }else{
            echo '1';
        }
    }
    /**
    * 全选
    *
    * @return void
    */
    public function change_all(){
        
        $uid=session("userid");
      
        $status=input("status");
         
        if($status == 0){
            db("cars")->where("uid",$uid)->setField("status",0);
         }else{
            db("cars")->where("uid",$uid)->setField("status",1);
         }
         echo '0';
    }
    /**
    * 增加购物车数量
    *
    * @return void
    */
    public function add()
    {
    
        $cid=input('cid');
        $res=db("cars")->where("cid",$cid)->setInc("num",1);
        if($res){
            echo '1';
        }else{
            echo '0';
        }
    
    }
    public function reduct()
    {
    
        $cid=input('cid');
        $res=db("cars")->where("cid",$cid)->setDec("num",1);
        if($res){
            echo '1';
        }else{
            echo '0';
        }
        
    }
    //删除购物车中的商品
    public function delete()
    {
        
        $id=input('id');
        $re=db("cars")->where("cid",$id)->find();
        if($re){
            $del=db("cars")->where("cid",$id)->delete();
            echo '0';
        }else{
            echo '1';
        }
    }
    /**
    * 去结算
    *
    * @return void
    */
    public function buy()
    {
        
        $uid=session("userid");

        $addr=db("addr")->where(["uid"=>$uid,"a_status"=>1])->find();

        $this->assign("addr",$addr);

        $money = 0 ;
        $res=db("cars")->alias("a")->where(["uid"=>$uid,"a.status"=>1])->where("up",1)->join("goods b","a.gid=b.id")->select();

        foreach($res as $k => $v){
            if($v['sid'] == 0){
                $res[$k]['price']=$v['xprice'];
            }else{
                $spec=db("goods_spec")->where(["sid"=>$v['sid'],"s_status"=>1])->find();
                if($spec){
                    $res[$k]['price']=$spec["s_xprice"];
                }else{
                    $res[$k]['price']=$v['xprice'];
                    $res[$k]['s_name']='';
                }
            }     
        }

        foreach($res as $v){
            $money+=$v['price']*$v['num'];
        }

        $money=sprintf("%.2f",$money);

        $this->assign("money",$money);

        $this->assign("res",$res);

        return $this->fetch();
    }
    /**
    * 购物车结算生产订单
    *
    * @return void
    */
    public function sdd()
    {
        $uid=session("userid");

        $express=db("express_dd")->where(["uid"=>$uid,"status"=>0])->find();

        if($express){
            echo '-1';exit();
        }

        $user=db("user")->where("uid",$uid)->find();

        $aid=input("aid");

        $addr=db("addr")->where("aid",$aid)->find();

        $collection=input("collection");

        $collection_money=input("collection_money");

        if($collection == 2){
            $collection_money=0;
        }

        $content=input("content");
        $ob=db("cars");
        $car_dd=db("car_dd");
        $res=$ob->where(["uid"=>$uid,"status"=>1])->select();
        $str=array();
        $ob_goods=db("goods");
        $gname="";
        $zprice=0;

        foreach ($res as $k=>$v){
            $gid=$v['gid'];
            
           db("cars")->where("cid",$v['cid'])->delete();
            
            $arr['gid']=$v['gid'];
            $arr['uid']=$v['uid'];
            $arr['num']=$v['num'];

            $good=$ob_goods->where("id",$v['gid'])->find();
//             $old_dd=db("car_dd")->where("gid=$gid and uid=$uid and status=1")->find();
//             if($old_dd){
//                 $del=$car_dd->where("did={$old_dd['did']}")->delete();
//             }

            $price=$good['xprice'];

            $arr['gcode']=$good['code'];
            $arr['gtype']=$good['type'];
            $arr['gspecs']=$good['specs'];
            $arr['simage']=$v['s_image'];
            $arr['price']=$price;
            $arr['zprice']=$price*$v['num'];
            $arr['gname']=$v['c_name'];
            $arr['code']="CK-".uniqid().$k;
            $arr['time']=time();
            $arr['aid']=$aid;

            $arr['content']=$content;

            $arr['username']=$addr['username'];

            $arr['phone']=$addr['phone'];

            $arr['addr']=$addr['addr'].$addr['addrs'];

            $arr['collection']=$collection;

            $arr['collection_money']=$collection_money;

            $arr['type']=$user['type'];

            $re=$car_dd->insert($arr);
            $str[$k]=$arr['code'];
            $zprice+=$arr['zprice'];
            $gname.=$v['c_name'];

        }
//         $old_all=db("car_dd")->where("uid=$uid and gid=0 and status=0")->find();
//         if($old_all){
//             $del=$car_dd->where("did={$old_all['did']}")->delete();
//         }
        $str1=implode(',', $str);
        $all['gid']='0';
        $all['uid']=$uid;
        $all['num']=1;
        $all['zprice']=$zprice;

        $all['gname']=$gname;
        $all['code']="AK-".uniqid().'a';
        $all['pay']=$str1;
        $all['time']=time();
        $all['aid']=$aid;

        $all['username']=$addr['username'];

        $all['phone']=$addr['phone'];

        $all['addr']=$addr['addr'].$addr['addrs'];

        $all['collection']=$collection;

        $all['collection_money']=$collection_money;

        $all['type']=$user['type'];

        $all['content']=$content;
        $re=$car_dd->insert($all);
        $did = db('car_dd')->getLastInsID();
        if($re){
            echo $did;
        }else{
            echo '0';
        }
    }
}