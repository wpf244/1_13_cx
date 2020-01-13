<?php
namespace app\index\controller;

use think\Db;
use think\Loader;
use think\Request;


Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');
Loader::import('WxPay.WxPay', EXTEND_PATH, '.JsApiPay.php');

class Pay extends BaseHome
{

    public function pay_dd()
    {
        $id=input("did");

        $re=db("car_dd")->where("did",$id)->find();

        $this->assign("re",$re);

        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $this->assign("user",$user);


       
        
        return $this->fetch();
    }
    public function settlement()
    {
        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $did=input("did");

        $re=db("car_dd")->where("did",$did)->find();

        $goods_money=$re['zprice'];

        $user_money=$user['money'];

        if($re['status'] ==1){

            if($goods_money > $user_money){

                echo '0';

            }else{

                $dd_uid=$re['uid'];

                if($uid == $dd_uid){

                    $data['fu_time']=time();

                    $data['status']=2;

                    $pay = $re['pay'];

                    $res = explode(",",$pay);

                    $arr['uid']=$re['uid'];
                    $arr['did']=$re['did'];
                    $arr['money']=$re['zprice'];
                    $arr['content']="订单消费";
                    $arr['time']=time();

                    Db::startTrans();
                    try{

                        //修改订单状态
                        \db("car_dd")->where("did",$did)->update($data);


                        foreach($res as $v){
                            $dd = db("car_dd")->where("code",$v)->find();
                            $uid = $dd['uid'];
                            $gid = $dd['gid'];
                            $did = $dd['did'];
                            $num = $dd['num'];
                            $re_d = db("car_dd")->where("did=$did")->update($data);

                            //增加销量
                            db("goods")->where("id=$gid")->setInc("sales",$num);

                            //减少库存

                            db("goods")->where("id=$gid")->setDec("kc",$num);



                        }

                        //修改用户账户余额
                        \db("user")->where("uid",$uid)->setDec("money",$goods_money);

                        //修改用户消费金额

                        \db("user")->where("uid",$uid)->setInc("consume_money",$goods_money);
                        //添加消费记录
                        \db("consume_log")->insert($arr);

                        echo '1';

                        // 提交事务
                        Db::commit();
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();

                        echo '2';
                    }

                }else{
                    echo '3';
                }

            }

        }else{
            echo '4';
        }


    }
    /**
     * 运费订单
     */
    public function express_dd()
    {

        $id=input("id");

        $re=db("express_dd")->where("id",$id)->find();

        $this->assign("re",$re);

        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $this->assign("user",$user);

        return $this->fetch();
    }

    /**
     * 支付运费
     */
    public function pay_express()
    {
        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $id=input("id");

        $re=db("express_dd")->where("id",$id)->find();

        $goods_money=$re['fare'];

        $user_money=$user['money'];

        if($re['status'] ==0){

            if($goods_money > $user_money){

                echo '0';

            }else{

                $dd_uid=$re['uid'];

                if($uid == $dd_uid){

                    $data['status']=1;

                    $arr['uid']=$re['uid'];
                    $arr['did']=$re['did'];
                    $arr['money']=$re['fare'];
                    $arr['content']="订单消费";
                    $arr['time']=time();

                    Db::startTrans();
                    try{

                        //修改订单状态
                        \db("express_dd")->where("id",$id)->update($data);


                        //修改用户账户余额
                        \db("user")->where("uid",$uid)->setDec("money",$goods_money);

                        //修改用户消费金额

                        \db("user")->where("uid",$uid)->setInc("consume_money",$goods_money);
                        //添加消费记录
                        \db("consume_log")->insert($arr);

                        echo '1';

                        // 提交事务
                        Db::commit();
                    } catch (\Exception $e) {
                        // 回滚事务
                        Db::rollback();

                        echo '2';
                    }

                }else{
                    echo '3';
                }

            }

        }else{
            echo '4';
        }
    }


}