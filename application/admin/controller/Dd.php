<?php
namespace app\admin\controller;
\header("content-type:text/html;charset=utf-8;");

use think\Db;
use think\Loader;
use think\Request;


Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');
Loader::import('WxPay.WxPay', EXTEND_PATH, '.JsApiPay.php');

class Dd extends BaseAdmin
{
    public function dai_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){
                
                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";

            $type=0;
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("type",$type);

        $pagenum=input("page");

        if(!$pagenum){
            $pagenum=0;
        }
        $this->assign("pagenum",$pagenum);
        
        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=1 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);


        
        return $this->fetch();
    }
    public function out(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }

        $pagenum=input("pagenum");

        if(!$pagenum){
            $pagenum=1;
        }

        $num=($pagenum-1)*20;

        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=1 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->limit($num,20)->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I");
        $arrHeader =  array("供货渠道","用户名","订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","订单备注");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            if($v['type'] == 1){
                $type="代理商";
            }else{
                $type="客服";
            }
            $objActSheet->setCellValue('A'.$k,$type);
            $objActSheet->setCellValue('B'.$k,$v['nickname']);
            $objActSheet->setCellValue('C'.$k,$v['code']);
            $objActSheet->setCellValue('D'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('E'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('F'.$k, $v['username']);
            $objActSheet->setCellValue('G'.$k, $v['phone']);
            $objActSheet->setCellValue('H'.$k, $v['addr']);
            $objActSheet->setCellValue('I'.$k, $v['content']);

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(50);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."待付款订单".".xlsx";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
        
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
           
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
            
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function detail()
    {
        $id=\input('id');
        $re=db("car_dd")->alias("a")->field("a.*,b.nickname")->where("did=$id")->join("user b","a.uid = b.uid")->find();

        $this->assign("re",$re);

        $express=\db("express")->order("id desc")->select();

        $this->assign("express",$express);

        $pay=$re['pay'];
        $res=\explode(",", $pay);
        $arr=array();
        foreach ($res as $v){
            $arr[]=db("car_dd")->alias('a')->field("a.*,b.kc")->where("a.code='$v'")->join("goods b","a.gid = b.id")->find();
        }
        $this->assign("list",$arr);



        return $this->fetch();
    }
    public function express_detail()
    {

        $did=input("did");

        $dd=\db("car_dd")->where("did",$did)->find();

        $this->assign("dd",$dd);

        $code=$dd['express_code'];

        $number=$dd['number'];

        $re=find_express($code,$number);

//        var_dump($re);

        if($re['message'] == 'ok'){
            $data=$re['data'];

        }else{

            $data[0]['time']=date("Y-m-d H:i:s");
            $data[0]['context']="暂无物流信息";

        }


//        var_dump($data);
//        $last=array_pop($data);
//
//        $first=array_shift($data);
//
//        $info=$this->PeriodTime($last,$first,1);

//        $data=end($data);

//        var_dump($data);

        $arr=[];

        foreach ($data as $k => $v){
            $arr[]=$k;


        }

        $keys=max($arr)+1;

        $this->assign("keys",$keys);

        $this->assign("data",$data);


        return $this->fetch();
    }




    public function delete()
    {
        $id=\input('id');
        $re=db("car_dd")->where("did=$id")->find();
        if($re){
            $del=db("car_dd")->where("did=$id")->delete();
            $pay=explode(",",$re['pay']);
          
            db("car_dd")->where("code","in",$pay)->delete();
            $this->redirect("dai_dd");
        }else{
            $this->redirect("dai_dd");
        }
    }
    public function change()
    {
        $id=\input('id');
        $re=db("car_dd")->where("did=$id")->find();
        if($re){

            $data['fu_time']=time();
            $data['status']=2;

            $pay=explode(",",$re['pay']);

            // 启动事务
            Db::startTrans();
            try{
                db("car_dd")->where("did=$id")->update($data);

                foreach($pay as $v){
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

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {
                // 回滚事务
                Db::rollback();
            }
             
            $this->redirect("dai_dd");
        }else{
            $this->redirect("dai_dd");
        }
    }
    public function fa_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }
        $this->assign("start",$start);
        $this->assign("end",$end);

        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("type",$type);

        $pagenum=input("page");

        if(!$pagenum){
            $pagenum=0;
        }
        $this->assign("pagenum",$pagenum);

        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=2 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

        $shop=db("goods_shop")->select();

        $this->assign("shop",$shop);
        
        return $this->fetch();
    }
    public function outf(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }

        $pagenum=input("pagenum");

        if(!$pagenum){
            $pagenum=1;
        }

        $num=($pagenum-1)*20;

        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=2 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->limit($num,20)->select();
//         var_dump($list);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I");
        $arrHeader =  array("供货渠道","用户名","订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","订单备注");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            if($v['type'] == 1){
                $type="代理商";
            }else{
                $type="客服";
            }
            $objActSheet->setCellValue('A'.$k,$type);
            $objActSheet->setCellValue('B'.$k,$v['nickname']);
            $objActSheet->setCellValue('C'.$k,$v['code']);
            $objActSheet->setCellValue('D'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('E'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('F'.$k, $v['username']);
            $objActSheet->setCellValue('G'.$k, $v['phone']);
            $objActSheet->setCellValue('H'.$k, $v['addr']);
            $objActSheet->setCellValue('I'.$k, $v['content']);
    
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(50);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."待发货订单".".xlsx";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function save()
    {
        $did=\input('did');
        $re=db("car_dd")->where("did=$did")->find();
        if($re){
           if($re['status'] == 1){
               $data['status']=2;
               $data['fa_time']=\time();
               $res=db("car_dd")->where("did=$did")->update($data);
               
               $pay=$re['pay'];
               $arr=\explode(",", $pay);
               foreach ($arr as $v){
                   $ress=db("car_dd")->where("code='$v'")->update($data);
               }
               
               $this->redirect('fa_dd');
           }else{
               $this->redirect('fa_dd');
           }
        }else{
            $this->redirect('fa_dd');
        }
    }
    public function shou_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }
        $this->assign("start",$start);
        $this->assign("end",$end);

        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("type",$type);

        $pagenum=input("page");

        if(!$pagenum){
            $pagenum=0;
        }
        $this->assign("pagenum",$pagenum);
        
        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=3 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

        $shop=db("goods_shop")->select();

        $this->assign("shop",$shop);
        
        return \view("shou_dd");
    }
    public function outh(){
        $map=[];


        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }
        $pagenum=input("pagenum");

        if(!$pagenum){
            $pagenum=1;
        }

        $num=($pagenum-1)*20;
    
        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=3 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->limit($num,20)->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I");
        $arrHeader =  array("供货渠道","用户名","订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","订单备注");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            if($v['type'] == 1){
                $type="代理商";
            }else{
                $type="客服";
            }
            $objActSheet->setCellValue('A'.$k,$type);
            $objActSheet->setCellValue('B'.$k,$v['nickname']);
            $objActSheet->setCellValue('C'.$k,$v['code']);
            $objActSheet->setCellValue('D'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('E'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('F'.$k, $v['username']);
            $objActSheet->setCellValue('G'.$k, $v['phone']);
            $objActSheet->setCellValue('H'.$k, $v['addr']);
            $objActSheet->setCellValue('I'.$k, $v['content']);
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(50);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."待收货订单".".xls";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function ping_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("shop_id",$shop_id);
    
        $list=db("car_dd")->alias('a')->where("status=3 and gid=0")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

        $shop=db("goods_shop")->select();

        $this->assign("shop",$shop);
    
        return $this->fetch();
    }
    public function outp(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
    
        $list=db("car_dd")->alias('a')->where("status=3 and gid=0")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H");
        $arrHeader =  array("订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","订单备注","商户名称");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['code']);
            $objActSheet->setCellValue('B'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('C'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('D'.$k, $v['username']);
            $objActSheet->setCellValue('E'.$k, $v['phone']);
            $objActSheet->setCellValue('F'.$k, $v['addr'].$v['addrs']);
            $objActSheet->setCellValue('G'.$k, $v['content']);
            $objActSheet->setCellValue('H'.$k, $v['sname']);
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."待评价订单".".xls";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function wan_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }
        $this->assign("start",$start);
        $this->assign("end",$end);

        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("type",$type);

        $pagenum=input("page");

        if(!$pagenum){
            $pagenum=0;
        }
        $this->assign("pagenum",$pagenum);
    
        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=4 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

    
        return $this->fetch();
    }
    public function outw(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');

        $addr=\input('addr');

        $type=input("type");

        if($start || $code ||  $addr || $type){
            if($start){

                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }

            if($addr){
                $map['addr|username|phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }

        }else{

            $start="";
            $end="";

            $addr="";
            $code="";

            $type=0;

        }

        $pagenum=input("pagenum");

        if(!$pagenum){
            $pagenum=1;
        }

        $num=($pagenum-1)*20;
    
        $list=db("car_dd")->alias('a')->field("a.*,c.nickname")->where("a.status=4 and gid=0")->where($map)->join("user c","a.uid = c.uid",'left')->order("did desc")->limit($num,20)->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I");
        $arrHeader =  array("供货渠道","用户名","订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","订单备注");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            if($v['type'] == 1){
                $type="代理商";
            }else{
                $type="客服";
            }
            $objActSheet->setCellValue('A'.$k,$type);
            $objActSheet->setCellValue('B'.$k,$v['nickname']);
            $objActSheet->setCellValue('C'.$k,$v['code']);
            $objActSheet->setCellValue('D'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('E'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('F'.$k, $v['username']);
            $objActSheet->setCellValue('G'.$k, $v['phone']);
            $objActSheet->setCellValue('H'.$k, $v['addr']);
            $objActSheet->setCellValue('I'.$k, $v['content']);
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(30);
        $objActSheet->getColumnDimension('H')->setWidth(50);
        $objActSheet->getColumnDimension('I')->setWidth(30);
    
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."已完成订单".".xls";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function tui_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("shop_id",$shop_id);
    
        // $list=db("car_dd")->alias('a')->where("status=5 and gid=0 and state=0")->where($map)->join("addr b","a.aid = b.aid","LEFT")->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $list=db("car_dd")->alias('a')->where("status=5 and gid=0 and state=0")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

        $shop=db("goods_shop")->select();

        $this->assign("shop",$shop);
    
        return \view("tui_dd");
    }
    public function ytui_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("shop_id",$shop_id);
    
        $list=db("car_dd")->alias('a')->where("status=5 and gid=0 and state=1")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

        $shop=db("goods_shop")->select();

        $this->assign("shop",$shop);
    
        return \view("ytui_dd");
    }
    public function bo_dd()
    {
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("shop_id",$shop_id);
    
        // $list=db("car_dd")->alias('a')->where("gid=0 and state=2")->where($map)->join("addr b","a.aid = b.aid","LEFT")->order("did desc")->paginate(20,false,['query'=>request()->param()]);

        $list=db("car_dd")->alias('a')->where("gid=0 and state=2")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->paginate(20,false,['query'=>request()->param()]);

        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);

        $shop=db("goods_shop")->select();

        $this->assign("shop",$shop);
    
        return $this->fetch();
    }
    public function tong()
    {
        $did=\input('id');
        $re=db("car_dd")->where("did=$did")->find();
        if($re['status'] == 5){
            $res=db("car_dd")->where("did=$did")->setField("state",1);
            $pay=$re['pay'];
            $pays=\explode(",", $pay);
            foreach ($pays as $v){
                db("car_dd")->where("code='$v'")->setField("state",1);
            }
            
            $out_trade_no=$re['code'];
            $total_fee=$re['zprice']*100;
            $refund_fee=$re['zprice']*100;

            $data=db("payment")->where("id",1)->find();

            $input = new \WxPayRefund();
            $input->SetOut_trade_no($out_trade_no);
            $input->SetTotal_fee($total_fee);
            $input->SetRefund_fee($refund_fee);
            $input->SetOut_refund_no("sdkphp".date("YmdHis"));
            $input->SetOp_user_id($data['mchid']);

            $order = \WxPayApi::refund($input,$data);

          //  var_dump($order);exit;

            $this->redirect("tui_dd");
        }else{
            $this->redirect("tui_dd");
        }
    }
    public function bo()
    {
        $did=\input('id');
        $re=db("car_dd")->where("did=$did")->find();
        if($re['status'] == 5){
            $data['status']=$re['old_status'];
            $data['state']=2;
            $res=db("car_dd")->where("did=$did")->update($data);
            $pay=$re['pay'];
            $pays=\explode(",", $pay);
            foreach ($pays as $v){
                db("car_dd")->where("code='$v'")->update($data);
            }
            $this->redirect("tui_dd");
        }else{
            $this->redirect("tui_dd");
        }
    }
    public function outs(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
    
        $list=db("car_dd")->alias('a')->where("status=5 and gid=0 and state=0")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J,K,L");
        $arrHeader =  array("订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","申请退货时间","退货原因","快递公司","快递号码","退货备注","商户名称");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['code']);
            $objActSheet->setCellValue('B'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('C'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('D'.$k, $v['username']);
            $objActSheet->setCellValue('E'.$k, $v['phone']);
            $objActSheet->setCellValue('F'.$k, $v['addr'].$v['addrs']);
            $objActSheet->setCellValue('G'.$k, \date("Y-m-d H:i:s",$v['t_time']));
            $objActSheet->setCellValue('H'.$k, $v['cencal']);
            $objActSheet->setCellValue('I'.$k, $v['express']);
            $objActSheet->setCellValue('J'.$k, $v['number']);
            $objActSheet->setCellValue('K'.$k, $v['remarks']);
            $objActSheet->setCellValue('L'.$k, $v['sname']);
    
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(25);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        $objActSheet->getColumnDimension('J')->setWidth(30);
        $objActSheet->getColumnDimension('K')->setWidth(30);
        $objActSheet->getColumnDimension('L')->setWidth(30);
    
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."申请退货订单".".xls";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }

    public function outb(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
    
        $list=db("car_dd")->alias('a')->where("gid=0 and state=2")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J,K,L");
        $arrHeader =  array("订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","申请退货时间","退货原因","快递公司","快递号码","退货备注","商户名称");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['code']);
            $objActSheet->setCellValue('B'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('C'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('D'.$k, $v['username']);
            $objActSheet->setCellValue('E'.$k, $v['phone']);
            $objActSheet->setCellValue('F'.$k, $v['addr'].$v['addrs']);
            $objActSheet->setCellValue('G'.$k, \date("Y-m-d H:i:s",$v['t_time']));
            $objActSheet->setCellValue('H'.$k, $v['cencal']);
            $objActSheet->setCellValue('I'.$k, $v['express']);
            $objActSheet->setCellValue('J'.$k, $v['number']);
            $objActSheet->setCellValue('K'.$k, $v['remarks']);
            $objActSheet->setCellValue('L'.$k, $v['sname']);
    
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(25);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        $objActSheet->getColumnDimension('J')->setWidth(30);
        $objActSheet->getColumnDimension('K')->setWidth(30);
        $objActSheet->getColumnDimension('L')->setWidth(30);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."已驳回订单".".xls";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    
    public function outsy(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $uid=session("uid");

        $shop_id=input("shop_id");
       
        $admin=db("admin")->where("id",$uid)->find();

        if($admin['level'] == 2){
             $map['shop_id']=['eq',$admin['shop_id']];
        }else{
            if($shop_id){
                $map['shop_id']=['eq',$shop_id];
            }
        }
       
        if($start || $code ||  $addr){
            if($start){
                
                $map['time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $map['code']=array('like','%'.$code.'%');
            }
         
            if($addr){
                $maps['addr|addrs|username|phone']=array('like','%'.$addr.'%');
                $re=db("addr")->where($maps)->select();
           
                    $id=array();
                    foreach ($re as $v){
                        $id[]=$v['aid'];
                    }
                    $map['a.aid']=array("in",$id);
          
            }
        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("shop_id",$shop_id);
    
        $list=db("car_dd")->alias('a')->where("status=5 and gid=0 and state=1")->where($map)->join("addr b","a.aid = b.aid","LEFT")->join("goods_shop c","a.shop_id=c.sid")->order("did desc")->select();
        // var_dump($data);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J,K,L");
        $arrHeader =  array("订单号","订单总金额","下单时间","收货人姓名","收货人电话","收货人地址","申请退货时间","退货原因","快递公司","快递号码","退货备注","商户名称");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['code']);
            $objActSheet->setCellValue('B'.$k, $v['zprice']);
            // 表格内容
            $objActSheet->setCellValue('C'.$k, \date("Y-m-d H:i:s",$v['time']));
            $objActSheet->setCellValue('D'.$k, $v['username']);
            $objActSheet->setCellValue('E'.$k, $v['phone']);
            $objActSheet->setCellValue('F'.$k, $v['addr'].$v['addrs']);
            $objActSheet->setCellValue('G'.$k, \date("Y-m-d H:i:s",$v['t_time']));
            $objActSheet->setCellValue('H'.$k, $v['cencal']);
            $objActSheet->setCellValue('I'.$k, $v['express']);
            $objActSheet->setCellValue('J'.$k, $v['number']);
            $objActSheet->setCellValue('K'.$k, $v['remarks']);
            $objActSheet->setCellValue('L'.$k, $v['sname']);
    
            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(20);
        $objActSheet->getColumnDimension('C')->setWidth(25);
        $objActSheet->getColumnDimension('D')->setWidth(25);
        $objActSheet->getColumnDimension('E')->setWidth(25);
        $objActSheet->getColumnDimension('F')->setWidth(30);
        $objActSheet->getColumnDimension('G')->setWidth(25);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->getColumnDimension('I')->setWidth(30);
        $objActSheet->getColumnDimension('J')->setWidth(30);
        $objActSheet->getColumnDimension('K')->setWidth(30);
        $objActSheet->getColumnDimension('L')->setWidth(30);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."已退货订单".".xls";
    
        $userBrowser=$_SERVER['HTTP_USER_AGENT'];
    
        if(preg_match('/MSIE/i', $userBrowser)){
            $outfile=urlencode($outfile);
             
        }else{
            $outfile= iconv("utf-8","gb2312",$outfile);;
    
        }
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function updates()
    {
        $id=\input('id');
        $re=db("car_dd")->where("did=$id")->find();
        $this->assign("re",$re);
        return \view("updates");
    }
    public function saved()
    {
        $did=input('did');
        $data=\input('post.');
        $re=db("car_dd")->where("did=$did")->find();
        if($re){
            $res=db("car_dd")->where("did=$did")->update($data);
            if($res){
                $this->success("修改成功",url('dai_dd'));
            }else{
                $this->error("修改失败",url('dai_dd'));
            }
        }else{
            $this->error("非法操作",url('dai_dd'));
        }
    }
    public function express()
    {
        $list = db('express')->order('id desc')->paginate(10);
        $this->assign("list",$list);

        return $this->fetch();
    }
    public function save_express(){
        if($this->request->isAjax()){
            $id=input("id");
            if($id){
                $data['express_name']=input("name");
                $data['express_code']=input("code");
                $res=db("express")->where("id",$id)->update($data);
                if($res){
                    $this->success("修改成功！",url('Dd/express'));
                }else{
                    $this->error("修改失败！",url('Dd/express'));
                }
            }else{
                $data['express_name']=input("name");
                $data['express_code']=input("code");
                $re=db("express")->insert($data);
                if($re){
                    $this->success("添加成功！",url('Dd/express'));
                }else{
                    $this->error("添加失败！",url('Dd/express'));
                }
            }

        }else{
            $this->success("非法提交",url('Dd/express'));
        }
    }
    public function modify_express(){
        $id=input('id');
        $re=db('express')->where("id=$id")->find();
        echo json_encode($re);
    }
    public function delete_express()
    {
        $id=input("id");

        $re=db("express")->where("id=$id")->find();


        if($re){
            $del=db("express")->where("id=$id")->delete();

            $this->redirect("express");
        }else{
            $this->redirect("express");
        }
    }
    //保存运费
    public function fare()
    {
        $did=input("did");

        $fare=input("fare");

        $re=db("car_dd")->where("did",$did)->find();

        $data['did']=$did;

        $data['uid']=$re['uid'];

        $data['fare']=$fare;

        $data['time']=time();

        $arr['uid']=$re['uid'];

        $arr['did']=$did;

        $arr['money']=$fare;

        $arr['content']="运费消费";

        $arr['time']=time();

        $arr['status']=2;

        $user=db("user")->where("uid",$re['uid'])->find();

        $user_money=$user['money'];

            // 启动事务
        Db::startTrans();
        try{

                if($user_money >= $fare){
                    $data['status']=1;

                    //增加消费日志
                    \db("consume_log")->insert($arr);

                    //修改用户余额
                    \db("user")->where("uid",$re['uid'])->setDec("money",$fare);

                    //修改用户消费余额
                    \db("user")->where("uid",$re['uid'])->setInc("consume_money",$fare);

                }
                //生产运费订单
                db("express_dd")->insert($data);



            db("car_dd")->where("did",$did)->setField("fare",$fare);

            $res = explode(",",$re['pay']);

            foreach ($res as $v){
                db("car_dd")->where("code",$v)->setField("fare",$fare);
            }

            echo '1';

            // 提交事务
            Db::commit();
        } catch (\Exception $e) {

            // 回滚事务
            Db::rollback();

            echo '2';
        }



    }
    //修改运费
    public function fares()
    {
        $did=input("did");

        $fare=input("fare");

        $re=db("car_dd")->where("did",$did)->find();

        //查询运费订单
        $express_dd=\db("express_dd")->where(["uid"=>$re['uid'],"did"=>$did])->find();

        //查询用户信息

        $user=\db("user")->where("uid",$re['uid'])->find();

        $arr['uid']=$re['uid'];

        $arr['did']=$did;

        $arr['money']=$fare;

        $arr['content']="运费消费";

        $arr['time']=time();

        $arr['status']=2;

        $money=$user['money'];

        if($express_dd['status'] == 0){

            // 启动事务
            Db::startTrans();
            try{

                if($money >= $fare){
                    $data['status']=1;

                    //增加消费日志
                    \db("consume_log")->insert($arr);

                    //修改用户余额
                    \db("user")->where("uid",$re['uid'])->setDec("money",$fare);

                    //修改用户消费余额
                    \db("user")->where("uid",$re['uid'])->setInc("consume_money",$fare);


                }else{
                    $data['status']=0;
                }
                $data['fare']=$fare;
                //生产运费订单
                db("express_dd")->where("id",$express_dd['id'])->update($data);



                db("car_dd")->where("did",$did)->setField("fare",$fare);

                $res = explode(",",$re['pay']);

                foreach ($res as $v){
                    db("car_dd")->where("code",$v)->setField("fare",$fare);
                }

                echo '1';

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {

                // 回滚事务
                Db::rollback();

                echo '2';
            }


        }else{


            echo  '3';
        }




    }


    //发货
    public function to_express()
    {
        $did=input("did");

        $express=input("express");

        $exp=\db("express")->where("id",$express)->find();

        $data['express']=$exp['express_name'];

        $data['express_code']=$exp['express_code'];

        $data['number']=input("number");

        $data['status']=3;

        $data['fa_time']=time();

        $re=\db("car_dd")->where("did",$did)->find();

        if($re['status'] == 2){

            // 启动事务
            Db::startTrans();
            try{

                db("car_dd")->where("did",$did)->update($data);

                $res = explode(",",$re['pay']);

                foreach ($res as $v){
                    db("car_dd")->where("code",$v)->update($data);
                }

                echo '1';

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {


                // 回滚事务
                Db::rollback();

                echo '2';
            }

        }else{
            echo '0';
        }

    }

    public function to_expresss()
    {
        $did = input("did");

        $express=input("express");

        $exp=\db("express")->where("id",$express)->find();

        $data['express']=$exp['express_name'];

        $data['express_code']=$exp['express_code'];

        $data['number'] = input("number");


        $re = \db("car_dd")->where("did", $did)->find();

        if ($re['status'] == 3) {

            // 启动事务
            Db::startTrans();
            try {

                db("car_dd")->where("did", $did)->update($data);

                $res = explode(",", $re['pay']);

                foreach ($res as $v) {
                    db("car_dd")->where("code", $v)->update($data);
                }

                echo '1';

                // 提交事务
                Db::commit();
            } catch (\Exception $e) {


                // 回滚事务
                Db::rollback();

                echo '2';
            }

        } else {
            echo '0';
        }


    }
    
    
    
    
    
    
}