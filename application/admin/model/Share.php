<?php
namespace app\admin\model;

use Think\Model;

class Share extends Model
{
    public function share($uid,$moneys)
    {
         $user=db("user")->where(["uid"=>$uid,"status"=>1,"is_delete"=>0])->find();
         if($user){
             $fid=$user['fid'];
             if($fid != 0){
                  //返利
                  $fuser=db("user")->where(["uid"=>$fid,"status"=>1,"is_delete"=>0])->find();
                  if($fuser){
                      $level=$fuser['level'];
                      //应返佣金
                      $money = $this->should_share($level,$moneys);

                      //用户增加佣金
                      db("user")->where("uid",$fid)->setInc("money",$money);

                      //增加佣金日志
                      $data['uid']=$fid;
                      $data['type']=1;
                      $data['money']=$money;
                      $data['oper']="一级分销增加佣金";
                      $data['time']=time();
                      $this->money_log($data);

                      //二级返利
                      $fids=$fuser['fid'];
                      if($fids != 0){
                          //返利
                        $fusers=db("user")->where(["uid"=>$fids,"status"=>1,"is_delete"=>0])->find();
                        if($fusers){
                            $levels=$fusers['level'];
                            //应返佣金
                            $moneys = $this->should_shares($levels,$moneys);

                            //用户增加佣金
                            db("user")->where("uid",$fids)->setInc("money",$moneys);

                            //增加佣金日志
                            $datas['uid']=$fids;
                            $datas['type']=1;
                            $datas['money']=$moneys;
                            $datas['oper']="二级分销增加佣金";
                            $datas['time']=time();
                            $this->money_log($datas);
                       }

                     }

                  }
             }
         }
    }

    /**
    * 一级应返佣金
    *
    * @return void
    */
    public function should_share($level,$moneys)
    {
         $share=db("share")->where("id",1)->find();
         if($level == 1){
             $money = $moneys*$share['level_1']/100;
         }
         if($level == 2){
            $money = $moneys*$share['level_2']/100;
        }
        if($level == 3){
            $money = $moneys*$share['level_3']/100;
        }
        return $money;
    }
      /**
    * 二级应返佣金
    *
    * @return void
    */
    public function should_shares($level,$moneys)
    {
         $share=db("share")->where("id",1)->find();
         if($level == 1){
             $money = $moneys*$share['level_12']/100;
         }
         if($level == 2){
            $money = $moneys*$share['level_22']/100;
        }
        if($level == 3){
            $money = $moneys*$share['level_32']/100;
        }
        return $money;
    }
    /**
    * 佣金日志
    *
    * @return void
    */
    public function money_log($data)
    {
        db("money_log")->insert($data);
    }
}