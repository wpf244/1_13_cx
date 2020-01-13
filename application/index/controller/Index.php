<?php
namespace app\index\controller;

class Index extends BaseUser
{
    public function index()
    {
        //banner图
        $lb=db("lb")->where(["fid"=>1,"status"=>1])->order(["sort asc","id desc"])->select();

        $this->assign("lb",$lb);

        $lbs=db("lb")->where("fid",2)->find();

        $this->assign("lbs",$lbs);

        //分类列表

        $type=db("goods_type")->where("type_recome",1)->order(["type_sort asc","type_id desc"])->limit(5)->select();

        $this->assign("type",$type);

        //商品列表

        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $canal=$user['type'];

        $goods=db("goods")->where(["status"=>1,"up"=>1,"canal"=>$canal])->order(["sort asc","id desc"])->select();

        $this->assign("goods",$goods);


        
        return $this->fetch();
    }
    public function kind()
    {
        $id=input("id");

        if(empty($id)){
            $id=0;
        }

        $this->assign("id",$id);

        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $canal=$user['type'];

        
        $list=db("goods_type")->order(["type_sort asc","type_id desc"])->select();

        foreach($list as $k =>$v){
            $list[$k]['goods']=db("goods")->where(["fid"=>$v['type_id'],"up"=>1,"canal"=>$canal])->order(["sort asc","id desc"])->select();
        }

        $this->assign("list",$list);

        
        return $this->fetch();
    }
    /**
    * 商品详情
    *
    * @return void
    */
    public function detail()
    {
        $id=\input("id");

        $re=db("goods")->where("id",$id)->find();

        $this->assign("re",$re);

        $img=db("goods_img")->where(["g_id"=>$id,"i_status"=>1])->select();

        $this->assign("img",$img);


        $lb=db("lb")->where(["fid"=>3,"status"=>1])->order(["sort asc","id asc"])->select();

        $this->assign("lb",$lb);

        $uid=\session("userid");

        if($uid){
            $rec=db("collect")->where(["gid"=>$id,"uid"=>$uid,"type"=>0])->find();

            if($rec){
                $collect=1;
            }else{
                $collect=0;
            }

         $car=db("cars")->where("uid",$uid)->count();

        }else{
            $collect=0;
            $car=0;
        }

        $this->assign("collect",$collect);

        $this->assign("car",$car);

        $spec=db("goods_spec")->where(["g_id"=>$id,"s_status"=>1])->select();

        $this->assign("spec",$spec);


        
        
        return $this->fetch();
    }
    /**
    * 商品收藏
    *
    * @return void
    */
    public function collect()
    {
        $uid=\session("userid");
        if($uid){
            $id=input("id");
            $re=db("collect")->where(["gid"=>$id,"uid"=>$uid])->find();

            if($re){
                db("collect")->where("id",$re['id'])->delete();
            }else{
                $data['gid']=$id;
                $data['uid']=$uid;

                db("collect")->insert($data);
            }
            echo '0';

        }else{
            echo '1';
        }
       
    }
    /**
    * 获取商品价格
    *
    * @return void
    */
    public function get_spec()
    {
        $sid=input("sid");

        $re=db("goods_spec")->where("sid",$sid)->find();

        if($re){
            echo json_encode($re);
        }else{
            echo '1';
        }
    }
    /**
    * 加入购物车
    *
    * @return void
    */
    public function join_car()
    {
        $uid=session("userid");
        if($uid){

            $gid=\input('gid');
            $num=\input('num');
            $sid=input('sid');
            $re=db("goods")->where("id",$gid)->find();
            $res=db("goods_spec")->where(["sid"=>$sid, "g_id"=>$gid])->find();

            if($re){
                $rec=db('cars')->where(["uid"=>$uid,"gid"=>$gid,"sid"=>$sid])->find();
                if($rec){
                    $del=db('cars')->where("cid",$rec['cid'])->setInc("num",$num);
                    if($del){
                        echo '0';
                    }else{
                        echo '2';
                    }
                }else{

                   $data['uid']=$uid;
                   $data['gid']=$gid;
                   $data['num']=$num;
                   $data['c_name']=$re['name'];
                  

                   if($sid == 0){
                    $data['price']=$re['xprice'];
                    $data['sid']=$sid;
                    $data['s_image']=$re['image'];
                   }else{
                    $data['price']=$res['s_xprice'];
                    $data['sid']=$sid;
                    $data['s_name']=$res['s_name'];
                    $data['s_image']=$res['s_image'];
                   }
                   
                   $rea=db("cars")->insert($data);

                   if($rea){
                       echo '0';
                   }else{
                       echo '2';
                   }

                }
                
            }else{
                echo '2';
            }

        }else{
            echo '1';
        }
    }
    /**
    * 立即购买
    *
    * @return void
    */
    public function join_buy()
    {
        $uid=\session("userid");

        if($uid){
           $re=db("dd")->where(["uid"=>$uid])->find();

           if($re){
            db("dd")->where(["uid"=>$uid])->delete();
           }
           $data['uid']=$uid;
           $data['gid']=input("gid");
           $data['num']=input("num");
           $data['sid']=input("sid");

           $rea=db("dd")->insert($data);
           $did=db("dd")->getLastInsID();
           if($rea){
                 echo $did;
           }else{
               echo '-1';
           }

        }else{
            echo '0';
        }
    }
    /**
    * 确认订单
    *
    * @return void
    */
    public function buy()
    {
        $uid=session("userid");

        if($uid){
            
            $addr=db("addr")->where(["uid"=>$uid,"a_status"=>1])->find();

            $this->assign("addr",$addr);

            $did=input("did");

            $re=db("dd")->where("id",$did)->find();

            $this->assign("re",$re);

            $gid=$re['gid'];

            $sid=$re['sid'];

            $goods=db("goods")->where("id",$gid)->find();

            if($sid == 0){
                $spec['s_xprice']=$goods['xprice'];
                $spec['s_name']='';
                $spec['s_image']=$goods['image'];
            }else{
                $spec=db("goods_spec")->where("sid",$sid)->find();
            }

            $this->assign("goods",$goods);

            $this->assign("spec",$spec);

            $prices=$spec['s_xprice']*$re['num'];

            $this->assign("prices",$prices);

            $this->assign("did",$did);

            return $this->fetch();
        }else{
            $this->redirect("Login/login");
        }
 
    }
    /**
    * 立即购买生产订单
    *
    * @return void
    */
    public function sdd()
    {
        $did=input('did');

        $aid=input("aid");

        $content=\input("content");

        $re=db("dd")->where("id",$did)->find();

        $gid=$re['gid'];
        $sid=$re['sid'];
        $num=$re['num'];
     
        $uid=session("userid");
        $ob=db("car_dd");
        // $old_dd=db("car_dd")->where(["gid"=>$gid,"uid"=>$uid,"status"=>0])->find();
        // if($old_dd){
        //     $del=$ob->where("did",$old_dd['did'])->delete();
          
        //     $dels=$ob->where("pay",$old_dd['code'])->find();
        //     if($dels){
        //         $delss=$ob->where("did",$dels['did'])->delete();
        //     }
        // }
        $good=db("goods")->where("id",$gid)->find();
        $spec=db("goods_spec")->where("sid",$sid)->find();

        

        if(empty($spec)){
            $price=$good['xprice'];
            $spec['s_name']='';
            $spec['s_image']=$good['image'];
        }else{
            $price=$spec['s_xprice'];
        }
     
        
        $arr=array();
        $arr['gid']=$gid;
        $arr['uid']=$uid;
        $arr['num']=$num;
        $arr['sid']=$sid;
        $arr['price']=$price;
        $arr['zprice']=$price*$num;
        $arr['gname']=$good['name'];
        $arr['sname']=$spec['s_name'];
        $arr['simage']=$spec['s_image'];
        $arr['code']="CK-".uniqid();
        $arr['content']=$content;
        $arr['aid']=$aid;
        $arr['shop_id']=$good['shop_id'];
        $arr['time']=time();
        
        $re=$ob->insert($arr);
        
        $all['gid']='0';
        $all['uid']=$uid;
        $all['num']=1;
        $all['sid']=$sid;
        $all['price']=$price;
        $all['gname']=$good['name'];
        $all['sname']=$spec['s_name'];
        $all['simage']=$spec['s_image'];
        $all['zprice']=$price*$num;
        $all['code']="AK-".uniqid().'a';
        $all['pay']=$arr['code'];
        $all['content']=$content;
        $all['aid']=$aid;
        $all['shop_id']=$good['shop_id'];
        $all['time']=time();

        db("dd")->where("id",$did)->delete();
        
        $rez=$ob->insert($all);

       
        
        $dids = db('car_dd')->getLastInsID();
        if($dids){
            echo $dids;
        }else{
            echo '0';
        }
    }
    /**
    * 商品搜索
    *
    * @return void
    */
    public function search()
    {
        
    //   cookie("history",null);
        
        $res=db("lb")->where(["fid"=>26,"status"=>1])->order(["sort asc","id desc"])->select();

        $this->assign("res",$res);
       // var_dump(cookie("history"));
        $history=array_filter(explode(",",cookie("history")));
    
        $this->assign("history",$history);

           
        return $this->fetch();
    }
    /**
    * 搜素结果页
    *
    * @return void
    */
    public function search_lister()
    {
        $keywords=input("keywords");

        $map["name|code"]=["like","%".$keywords."%"];

        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $canal=$user['type'];

        //商品列表
        $goods=db("goods")->where(["up"=>1,"canal"=>$canal])->where($map)->order(["sort asc","id desc"])->select();

        $this->assign("goods",$goods);

        $history=cookie("history");

        if(empty($history)){
            cookie("history",$keywords);
        }else{
            // var_dump(cookie("history"));
            $arr=explode(",",$history);
            
            $arr[]=$keywords;

            $arrs=implode(",",$arr);

            cookie("history",$arrs);
        }

        return $this->fetch();
    }
    public function clear()
    {
        cookie("history",null);
    }

















}