<?php
namespace app\index\controller;

use think\Db;

class User extends BaseUser
{
    /**
    * 个人中心
    *
    * @return void
    */
    public function index()
    {
        $uid=session("userid");

        $re=db("user")->where("uid",$uid)->find();

        $this->assign("re",$re);

        $city=session("city_index");

        $com=db("culture_city")->where("c_name",$city)->find();

        $this->assign("com",$com);
        
        return $this->fetch();
    }
    /**
    * 通用设置
    *
    * @return void
    */
    public function my_setting()
    {
        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $this->assign("user",$user);
        
        return $this->fetch();
    }
    /**
    * 修改密码
    *
    * @return void
    */
    public function pwd_change()
    {
        return $this->fetch();
    }
    /**
    * 保存密码
    *
    * @return void
    */
    public function pwd_save()
    {
        $uid=session("userid");

        $old_pwd=input("old_pwd");

        $pwd=input("pwd");

        $re=db("user")->where(["uid"=>$uid,"pwd"=>$old_pwd])->find();

        if($re){
            $res=db("user")->where("uid",$uid)->update(["pwd"=>$pwd]);

            if($res){
                echo '0';
            }else{
                echo '2';
            }

        }else{
            echo '1';
        }
    }
    /**
    * 手机号码换绑
    *
    * @return void
    */
    public function tel_change()
    {
        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $this->assign("user",$user);
        
        return $this->fetch();
    }
    /**
    * 发送验证码
    *
    * @return void
    */
    public function send_code()
    {
        $uid=session("userid");
        $phone=input('phone');
        $re=db('user')->where(["phone"=>$phone,"uid"=>["neq",$uid]])->find();
        if($re){
            echo '0';
        }else{
            $code =mt_rand(100000,999999);       
            $data['phone']=$phone;
            $data['code']=$code;
            $data['time']=time();
            $re=\db("sms_code")->where("phone",$phone)->find();
            if($re){
                $del=db("sms_code")->where("phone",$phone)->delete();
            }
            $rea=db("sms_code")->insert($data);
            Post($phone,$code);
            echo '1';
           
        }
    }
    /**
    * 换绑手机号
    *
    * @return void
    */
    public function phone_save()
    {
        $uid=session("userid");
        $phone=input("phone");
        $reu=db("user")->where(["phone"=>$phone,"uid"=>['neq',$uid]])->find();
        $code=input("code");
        $re=db("sms_code")->where(['phone'=>$phone,'code'=>$code])->find();
        if($re){
            $time=$re['time'];
            $times=time();
            $c_time=($times-$time);
            if($c_time < 300){
                db("sms_code")->where("id",$re['id'])->delete();
                if($reu){
                    echo '4';
                }else{
                  $data['phone']=input("phone");
               
                  $rea=db("user")->where("uid",$uid)->update($data);

                  if($rea){
                      
                       echo '0';
                  }else{
                    echo '3';
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
    * 个人信息修改
    *
    * @return void
    */
    public function infor_change()
    {
        
        $uid=session("userid");

        $user=db("user")->where("uid",$uid)->find();

        $this->assign("user",$user);
        
        return $this->fetch();
    }
    /**
    * 保存修改
    *
    * @return void
    */
    public function save_info()
    {
        $uid=session("userid"); 
        
        $file=request()->file("image");

        if($file){

          $data['image']=uploads("image");
            
        }
        $data['nickname']=input("nickname");

        $res=db("user")->where("uid",$uid)->update($data);

        if($res){
          $this->success("修改成功");
        }else{
           $this->error("修改失败");
        }
    }
    /**
    * 意见反馈
    *
    * @return void
    */
    public function feed_back()
    {
        
        $re=db("lb")->where("id",1)->find();

        $this->assign("re",$re);
        
        return $this->fetch();
    }
    /**
    * 保存意见反馈
    *
    * @return void
    */
    public function save_feed()
    {
        $uid=session("userid");
        $data=input("post.");
        $data['time']=time();
        $data['uid']=$uid;

        $re=db("opinion")->where("uid",$uid)->whereTime("time","d")->find();

        if(empty($re)){
            $rea=db("opinion")->insert($data);

            if($rea){
                echo '0';
            }else{
                echo '1';
            }
        }else{
            echo '1';
        }
        
    }
    /**
    * 我的优惠券
    *
    * @return void
    */
    public function my_coupon()
    {
        
        //全部优惠券
        $uid=session("userid");

        $res=db("user_coupon_only")->alias("a")->field("a.*,b.name")->where("uid",$uid)->join("hotel b","a.hid=b.id")->select();

        $this->assign("res",$res);

        $rey=db("user_coupon_only")->alias("a")->field("a.*,b.name")->where(["uid"=>$uid,"a.status"=>1])->join("hotel b","a.hid=b.id")->select();

        $this->assign("rey",$rey);

        $rew=db("user_coupon_only")->alias("a")->field("a.*,b.name")->where(["uid"=>$uid,"a.status"=>0])->join("hotel b","a.hid=b.id")->select();

        $this->assign("rew",$rew);
        
        return $this->fetch();
    } 
    /**
    * 我的发布
    *
    * @return void
    */
    public function my_publish()
    {
        $uid=session("userid");
        
        $res=db("publish")->alias("a")->field("a.*,b.nickname,b.image as photo")->where(["a.uid"=>$uid])->join("user b","a.uid=b.uid")->order(["id desc"])->select();


        $this->assign("res",$res);
        
        return $this->fetch();
    }
    public function delete_publish()
    {
        $id=input("id");

        $del=db("publish")->where("id",$id)->delete();

        if($del){
            echo '0';
        }else{
            echo '1';
        }
    }
    public function update_publish()
    {
        $id=input("id");

        $re=db("publish")->where("id",$id)->find();

        $this->assign("re",$re);
        
        return $this->fetch();
    }
    public function save_publish()
    {
        $data=input("post.");

        $id=input("id");

        $re=db("publish")->where("id",$id)->find();

        $arr=array();

        $files = request()->file('image');
        if($files){
            foreach($files as $file){
                // 移动到框架应用根目录/public/uploads/ 目录下
                $info = $file->validate(['size'=>31457280,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');
                if($info){
                    // 成功上传后 获取上传信息
                    $pa=$info->getSaveName();
                    $path=str_replace("\\", "/", $pa);
                    $paths='/uploads/'.$path;
                    $images=\think\Image::open(ROOT_PATH.'/public'.$paths);
                    $images->thumb(414,210,\think\Image::THUMB_CENTER)->save(ROOT_PATH.'/public'.$paths);
                    $arr[]=$paths;
                }else{
                    // 上传失败获取错误信息
                    $this->error($file->getError());
                }    
            }
            $data['image']=$arr[0];
            $data['images']=implode(",",$arr);
        }else{
            $data['images']=$re['images'];
            $data['image']=$re['image'];
        }
        

        $addrs=input("addr");

        $addr=str_replace(' ', '', $addrs);


        $result=$this->query_address($addr);

        $data['longs']=$result['lng'];

        $data['lats']=$result['lat'];

        $data['addr']=$addr;
 
 
        $re=db("publish")->where("id",$id)->update($data);

        if($re){
            $this->success("修改成功",url('my_publish'));
        }else{
            $this->error("系统繁忙,请稍后再试");
        }
    }
    public function query_address($addr){
        $key_words=$addr;
        $header[] = 'Referer: http://lbs.qq.com/webservice_v1/guide-suggestion.html';
        $header[] = 'User-Agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_3) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/66.0.3359.139 Safari/537.36';
        $url ="http://apis.map.qq.com/ws/place/v1/suggestion/?&region=&key=OB4BZ-D4W3U-B7VVO-4PJWW-6TKDJ-WPB77&keyword=".$key_words; 
 
        $ch = curl_init();
        //设置选项，包括URL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_HTTPHEADER,$header);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
 
        //执行并获取HTML文档内容
        $output = curl_exec($ch);
        
        curl_close($ch);
        
        $result = json_decode($output,true);

       // var_dump($result);exit;

        if(!empty($result['data'][0])){
            return $result['data'][0]['location'];
        }else{
            echo '0';
        }
        
      
       

    }
    /**
    * 酒店订单
    *
    * @return void
    */
    public function hotel_dd()
    {
        $uid=session("userid");

        $re=db("order")->where(["uid"=>$uid,"type"=>3,"status"=>0])->order(["id desc"])->select();

        $this->assign("re",$re);

        $res=db("order")->where(["uid"=>$uid,"type"=>3,"status"=>1])->order(["id desc"])->select();

        $this->assign("res",$res);

        $reh=db("order")->where(["uid"=>$uid,"type"=>3,"status"=>2])->order(["id desc"])->select();

        $this->assign("reh",$reh);

        
        return $this->fetch();
    }
    /**
    * 取消订单
    *
    * @return void
    */
    public function delete_dd()
    {
        $id=\input('id');
        $re=db("order")->where("id=$id")->find();
        if($re){
            $del=db("order")->where("id=$id")->delete();
            echo '0';
        }else{
            echo '1';
        }
    }
    /**
    * 团游订单
    *
    * @return void
    */
    public function package_dd()
    {
        $uid=session("userid");

        $re=db("order")->where(["uid"=>$uid,"type"=>1,"status"=>0])->order(["id desc"])->select();

        $this->assign("re",$re);

        $res=db("order")->where(["uid"=>$uid,"type"=>1,"status"=>1])->order(["id desc"])->select();

        $this->assign("res",$res);

        $reh=db("order")->where(["uid"=>$uid,"type"=>1,"status"=>2])->order(["id desc"])->select();

        $this->assign("reh",$reh);

        
        return $this->fetch();
    }
     /**
    * 门票订单
    *
    * @return void
    */
    public function spot_dd()
    {
        $uid=session("userid");

        $re=db("order")->where(["uid"=>$uid,"type"=>2,"status"=>0])->order(["id desc"])->select();

        $this->assign("re",$re);

        $res=db("order")->where(["uid"=>$uid,"type"=>2,"status"=>1])->order(["id desc"])->select();

        $this->assign("res",$res);

        $reh=db("order")->where(["uid"=>$uid,"type"=>2,"status"=>2])->order(["id desc"])->select();

        $this->assign("reh",$reh);

        
        return $this->fetch();
    }
    /**
    * 美食订单
    *
    * @return void
    */
    public function food_dd()
    {
        $uid=session("userid");

        $re=db("order")->where(["uid"=>$uid,"type"=>4,"status"=>0])->order(["id desc"])->select();

        $this->assign("re",$re);

        $res=db("order")->where(["uid"=>$uid,"type"=>4,"status"=>1])->order(["id desc"])->select();

        $this->assign("res",$res);

        $reh=db("order")->where(["uid"=>$uid,"type"=>4,"status"=>2])->order(["id desc"])->select();

        $this->assign("reh",$reh);

        
        return $this->fetch();
    }
    /**
    * 砍价订单
    *
    * @return void
    */
    public function bargain_dd()
    {
        $uid=session("userid");

        //未付款订单
        $rew=db("bargain_dd")->where(["uid"=>$uid,"status"=>0])->order(["id desc"])->select();

        $this->assign("rew",$rew);

        $res=db("bargain_dd")->where(["uid"=>$uid,"status"=>1])->order(["id desc"])->select();

        $this->assign("res",$res);

        $ref=db("bargain_dd")->where(["uid"=>$uid,"status"=>2])->order(["id desc"])->select();

        $this->assign("ref",$ref);

        $rec=db("bargain_dd")->where(["uid"=>$uid,"status"=>3])->order(["id desc"])->select();

        $this->assign("rec",$rec);


        
        return $this->fetch();
    }
    /**
    * 取消订单
    *
    * @return void
    */
    public function delete_bargain_dd()
    {
        $id=\input('id');
        $re=db("bargain_dd")->where("id=$id")->find();
        if($re){
            $del=db("bargain_dd")->where("id=$id")->delete();
            echo '0';
        }else{
            echo '1';
        }
    }
    /**
    * 确认收货
    *
    * @return void
    */
    public function change_bargain_dd()
    {
        $id=\input('id');
        $re=db("bargain_dd")->where("id",$id)->find();
        if($re){
           if($re['status'] == 2){
            $admin=db("admin")->where("id",$re['shop_id'])->find();

            $money=$re['price'];

            Db::startTrans();
            try{
              
               if($admin){
                   db("admin")->where(["id"=>$re['shop_id']])->setInc("money",$money);
               }
               db("bargain_dd")->where("id",$id)->setField("status",3);
          
               echo '0';
                // 提交事务
                Db::commit();    
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                echo '3';
            }
            
           } else{
               echo '2';
           }
           
        }else{
            echo '1';
        }
    }
    /**
    * 拼团订单
    *
    * @return void
    */
    public function assemble_dd()
    {
        $uid=session("userid");

        //拼团中
        $rew=db("assemble_dd")->where(["uid"=>$uid,"status"=>1])->order(["id desc"])->select();

        $this->assign("rew",$rew);

        //待发货

        $res=db("assemble_dd")->where(["uid"=>$uid,"status"=>2])->order(["id desc"])->select();

        $this->assign("res",$res);

        //待收货

        $ref=db("assemble_dd")->where(["uid"=>$uid,"status"=>3])->order(["id desc"])->select();

        $this->assign("ref",$ref);

        //已完成

        $rec=db("assemble_dd")->where(["uid"=>$uid,"status"=>4])->order(["id desc"])->select();

        $this->assign("rec",$rec);

         //拼团失败

         $rep=db("assemble_dd")->where(["uid"=>$uid,"status"=>5])->order(["id desc"])->select();

         $this->assign("rep",$rep);


        
        return $this->fetch();
    }
    /**
    * 确认收货
    *
    * @return void
    */
    public function change_assemble_dd()
    {
        $id=\input('id');
        $re=db("assemble_dd")->where("id",$id)->find();
        if($re){
           if($re['status'] == 3){
            $admin=db("admin")->where("id",$re['shop_id'])->find();

            $money=$re['price'];

            Db::startTrans();
            try{
              
               if($admin){
                   db("admin")->where(["id"=>$re['shop_id']])->setInc("money",$money);
               }
               db("assemble_dd")->where("id",$id)->setField("status",4);
          
               echo '0';
                // 提交事务
                Db::commit();    
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();

                echo '3';
            }
           
          
           } else{
               echo '2';
           }
           
        }else{
            echo '1';
        }
    }

    //我的收藏
    public function my_collcet(){

        $uid=session("userid");
        
        $res1=db("collect")->alias("a")->field("a.*,b.title,b.content")->where(["a.uid"=>$uid,"type"=>1])->join("rural b","a.gid=b.id")->select();

        $this->assign("res1",$res1);

        $res2=db("collect")->alias("a")->field("a.*,b.name as title,b.content")->where(["a.uid"=>$uid,"type"=>3])->join("spot b","a.gid=b.id")->select();

        $this->assign("res2",$res2);

        $res3=db("collect")->alias("a")->field("a.*,b.name as title,b.marray as content")->where(["a.uid"=>$uid,"type"=>4])->join("food b","a.gid=b.id")->select();

        $this->assign("res3",$res3);

        $res4=db("collect")->alias("a")->field("a.*,b.name as title,b.content")->where(["a.uid"=>$uid,"a.type"=>2])->join("hotel b","a.gid=b.id")->select();

        $this->assign("res4",$res4);

        return $this->fetch();
    }
    //取消收藏
    public function delete_collect()
    {
        $id=input("id");

        $del=db("collect")->where("id",$id)->delete();

        if($del){
            echo '0';
        }else{
            echo '1';
        }
    }
    //我的评论
    public function my_comment(){
        $uid=session("userid");
        
        $res1=db("assess")->alias("a")->field("a.*,b.title")->where(["a.u_id"=>$uid,"type"=>1])->join("rural b","a.g_id=b.id")->select();

        $this->assign("res1",$res1);

        $res2=db("assess")->alias("a")->field("a.*,b.name as title")->where(["a.u_id"=>$uid,"type"=>3])->join("spot b","a.g_id=b.id")->select();

        $this->assign("res2",$res2);

        $res3=db("assess")->alias("a")->field("a.*,b.name as title")->where(["a.u_id"=>$uid,"type"=>4])->join("food b","a.g_id=b.id")->select();

        $this->assign("res3",$res3);

        $res4=db("assess")->alias("a")->field("a.*,b.name as title")->where(["a.u_id"=>$uid,"a.type"=>2])->join("hotel b","a.g_id=b.id")->select();

        $this->assign("res4",$res4);
        

        return $this->fetch();
    }
    public function delete_assess()
    {
        $id=input("id");

        $del=db("assess")->where("id",$id)->delete();

        if($del){
            echo '0';
        }else{
            echo '1';
        }
    }
    
}