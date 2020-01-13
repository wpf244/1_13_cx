<?php
namespace app\admin\controller;

class Market extends BaseAdmin
{
    public function dd()
    {
        
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $type=input("type");

        $goods=input("goods");

        if($start || $code ||  $addr || $type || $goods){
            if($start){
                
                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $res=db("user")->field("uid")->where("nickname","like","%".$code."%")->select();

                $arr=[];
                foreach($res as $v){
                    $arr[]=$v['uid'];
                }
                $map['a.uid']=["in",$arr];
                
            }
         
            if($addr){
                $map['a.addr|username|a.phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }
            if($goods){
                $map['gname|gcode']=array('like','%'.$goods.'%');
            }

        }else{
            
            $start="";
            $end="";
        
            $addr="";
            $code="";

            $goods="";

            $type=0;
           
        }
        $this->assign("start",$start);
        $this->assign("end",$end);
      
        $this->assign("addr",$addr);
        $this->assign("code",$code);

        $this->assign("type",$type);

        $this->assign("goods",$goods);
        
        $where["gid"]=['neq',0];

        $where["a.status"]=['gt',1];

        $pagesize=input("pagesize");

        if(!$pagesize){
            $pagesize=10;
        }

        $this->assign("pagesize",$pagesize);

        $pagenum=input("page");

        if(!$pagenum){
            $pagenum=0;
        }
        $this->assign("pagenum",$pagenum);

        $list=db("car_dd")->alias("a")->field("a.*,b.nickname")->where($where)->where($map)->join("user b","a.uid = b.uid","left")->order("did asc")->paginate($pagesize,false,['query'=>request()->param()]);

        $this->assign("list",$list);

        $page=$list->render();

        $this->assign("page",$page);

        return $this->fetch();
    }
    /**
     * 导出数据
     *
     * @return void
     */
    public function out(){
        $map=[];

        $start=input('start');
        $end=input('end');
        $code=\input('code');
      
        $addr=\input('addr');

        $type=input("type");

        $goods=input("goods");

        if($start || $code ||  $addr || $type || $goods){
            if($start){
                
                $map['a.time']=['between time',[$start.'00:00:01',$end.'23:59:59']];
            }
            if($code){
                $res=db("user")->field("uid")->where("nickname","like","%".$code."%")->select();

                $arr=[];
                foreach($res as $v){
                    $arr[]=$v['uid'];
                }
                $map['a.uid']=["in",$arr];
                
            }
         
            if($addr){
                $map['a.addr|username|a.phone']=array('like','%'.$addr.'%');

            }

            if($type){
                $map['a.type']=['eq',$type];
            }
            if($goods){
                $map['gname|gcode']=array('like','%'.$goods.'%');
            }

        }
       
        $where["gid"]=['neq',0];

        $where["a.status"]=['gt',1];

        $pagesize=input("pagesize");

        if(!$pagesize){
            $pagesize=10;
        }

        $pagenum=input("pagenum");

        if(!$pagenum){
            $pagenum=1;
        }

        $num=($pagenum-1)*$pagesize;

      
        $list=db("car_dd")->alias("a")->field("a.*,b.nickname")->where($where)->where($map)->join("user b","a.uid = b.uid","left")->order("did asc")->limit($num,$pagesize)->select();
        // var_dump($num,$pagesize);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R");
        $arrHeader =  array("日期","业务员","供货渠道","收货地址","收货人","电话","商品代码","商品名称","规格型号","购买数量","出厂单价","出厂金额","备注","是否代收","代收金额","发货方式","快递单号","快递费");
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
            if($v['collection'] == 1){
                $collection="代收";
                $collection_money=$v['collection_money'];
            }else{
                $collection="不代收";
                $collection_money=0;
            }
            $objActSheet->setCellValue('A'.$k,\date("Y-m-d",$v['time']));
            $objActSheet->setCellValue('B'.$k,$v['nickname']);
            $objActSheet->setCellValue('C'.$k,$type);
            $objActSheet->setCellValue('D'.$k, $v['addr']);
            // 表格内容
            $objActSheet->setCellValue('E'.$k, $v['username']);
            $objActSheet->setCellValue('F'.$k, $v['phone']);
            $objActSheet->setCellValue('G'.$k, $v['gcode']);
            $objActSheet->setCellValue('H'.$k, $v['gname']);
            $objActSheet->setCellValue('I'.$k, $v['gtype']);
            $objActSheet->setCellValue('J'.$k,$v['num']);
            $objActSheet->setCellValue('K'.$k,$v['price']);
            $objActSheet->setCellValue('L'.$k,$v['zprice']);
            $objActSheet->setCellValue('M'.$k, $v['content']);
            $objActSheet->setCellValue('N'.$k, $collection);
            $objActSheet->setCellValue('O'.$k, $collection_money);
            $objActSheet->setCellValue('P'.$k, $v['express']);
            $objActSheet->setCellValue('Q'.$k, ' '.$v['number']);
            $objActSheet->setCellValue('R'.$k, $v['fare']);

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(10);
        $objActSheet->getColumnDimension('C')->setWidth(10);
        $objActSheet->getColumnDimension('D')->setWidth(50);
        $objActSheet->getColumnDimension('E')->setWidth(10);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(10);
        $objActSheet->getColumnDimension('H')->setWidth(30);
        $objActSheet->getColumnDimension('I')->setWidth(20);
        $objActSheet->getColumnDimension('J')->setWidth(20);
        $objActSheet->getColumnDimension('K')->setWidth(20);
        $objActSheet->getColumnDimension('L')->setWidth(25);
        $objActSheet->getColumnDimension('M')->setWidth(25);
        $objActSheet->getColumnDimension('N')->setWidth(25);
        $objActSheet->getColumnDimension('O')->setWidth(30);
        $objActSheet->getColumnDimension('P')->setWidth(30);
        $objActSheet->getColumnDimension('Q')->setWidth(20);
        $objActSheet->getColumnDimension('R')->setWidth(10);
        if($start !=0 ){
             
            $times=($start."-".$end);
        }else{
            $times="";
        }
        $outfile = "$times"."订单".".xlsx";
    
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
    /**
     * 订单详情
     */
    public function detail()
    {
        $did=input("id");

        $re=db("car_dd")->alias("a")->field("a.*,b.nickname")->where(["did"=>$did,"a.status"=>['gt',1]])->join("user b","a.uid = b.uid","left")->find();

        $this->assign("re",$re);
        
        return $this->fetch();
    }
    /**
     * 商品销售
     */
    public function goods()
    {
        $sort=input("sort");

        if($sort){

            if($sort ==1){

                $order="sales desc";

            }else{

                $order="sales asc";

            }

        }else{
            $order="sales desc";

            $sort=2;
        }

        $this->assign("sort",$sort);

        $pagesize=input("pagesize");

        if(!$pagesize){

            $pagesize=10;

        }
        $this->assign("pagesize",$pagesize);

        $pagenum=input("page");

        if(!$pagenum){
            $pagenum=0;
        }
        $this->assign("pagenum",$pagenum);

        $list=db("goods")->order("$order")->paginate($pagesize,false,['query'=>request()->param()]);

        $this->assign("list",$list);

        $page=$list->render();

        $this->assign("page",$page);
        
        return $this->fetch();
    }
    /**
     * 导出数据
     */
    public function outg()
    {
        $sort=input("sort");

        if($sort){

            if($sort ==1){

                $order="sales desc";

            }else{

                $order="sales asc";

            }

        }else{
            $order="sales desc";

            $sort=2;
        }

        $pagesize=input("pagesize");

        if(!$pagesize){

            $pagesize=10;

        }

        $pagenum=input("pagenum");

        if(!$pagenum){
            $pagenum=1;
        }

        $num=($pagenum-1)*$pagesize;
     
        $list=db("goods")->order("$order")->limit($num,$pagesize)->select();

        // var_dump($num,$pagesize);exit;
        vendor('PHPExcel.PHPExcel');//调用类库,路径是基于vendor文件夹的
        vendor('PHPExcel.PHPExcel.Worksheet.Drawing');
        vendor('PHPExcel.PHPExcel.Writer.Excel2007');
        $objExcel = new \PHPExcel();
        //set document Property
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel2007');
    
        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G");
        $arrHeader =  array("供货渠道","商品名称","商品价格","商品代码","商品规格","商品销量","商品库存");
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        }
        //填充表格信息
        foreach($list as $k=>$v){
            $k +=2;
            if($v['canal'] == 1){
                $type="代理商";
            }else{
                $type="客服";
            }
           
            $objActSheet->setCellValue('A'.$k,$type);
            $objActSheet->setCellValue('B'.$k,$v['name']);
            $objActSheet->setCellValue('C'.$k,$v['xprice']);
            $objActSheet->setCellValue('D'.$k, $v['code']);
            // 表格内容
            $objActSheet->setCellValue('E'.$k, $v['type']);
            $objActSheet->setCellValue('F'.$k, $v['sales']);
            $objActSheet->setCellValue('G'.$k, $v['kc']);
           

            // 表格高度
            $objActSheet->getRowDimension($k)->setRowHeight(20);
        }
    
        $width = array(20,20,15,10,10,30,10,15,15,15);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth(20);
        $objActSheet->getColumnDimension('B')->setWidth(30);
        $objActSheet->getColumnDimension('C')->setWidth(10);
        $objActSheet->getColumnDimension('D')->setWidth(20);
        $objActSheet->getColumnDimension('E')->setWidth(20);
        $objActSheet->getColumnDimension('F')->setWidth(20);
        $objActSheet->getColumnDimension('G')->setWidth(10);
       
        
        $outfile = "商品销量".".xlsx";
    
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


}