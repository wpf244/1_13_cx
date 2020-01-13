<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 删除图片
 */
function deleteImg($oldImg){
    if($oldImg != ''){
        $path = ROOT_PATH . 'public' . DS .$oldImg;
        if ($path != ROOT_PATH . 'public' . DS) {
            if(is_file($path) == true) {
                unlink($path);
            }
        }
    }
}
/**
 * 上传图片
 * **/
function uploads($image){
    $file = request()->file("$image");
    $info = $file->validate(['size'=>31457280,'ext'=>'jpg,png,gif,jpeg'])->move(ROOT_PATH . 'public' . DS . 'uploads');

    $pa=$info->getSaveName();
    $path=str_replace("\\", "/", $pa);
    $paths='/uploads/'.$path;
    $images=\think\Image::open(ROOT_PATH.'/public'.$paths);
    $images->save(ROOT_PATH.'/public'.$paths,null,60,true);

    return $paths;
   
    
}
/**
 * 检测目录读写权限
 * @param unknown $dir_path
 */
function check_dir_iswritable($dir_path) {
    // 目录路径
	$dir_path = str_replace("\\", "/", $dir_path);
	// 是否可写
	$is_writale = 1;
	// 判断是否是目录
    if(!is_dir($dir_path)){ 
		$is_writale=0; 
		return $is_writale; 
	}else{ 
	    $fp = fopen("$dir_path/test.txt", 'w');
        if($fp) {
            fclose($fp);
            unlink("$dir_path/test.txt");
            $writeable = 1; 
        } else {
            $writeable = 0;
        }
	} 
	return $is_writale;
}
/**
 * 发送短信
 * */
function Post($phone,$code){ 
    $post_data = array();
    $post_data['userid'] = 18799;
    $post_data['account'] = '会管家';
    $post_data['password'] = '123456';
    $post_data['content'] = '【宇控智能】您的验证码为'.$code.'，请您在5分钟内完成操作。'; //短信内容需要用urlencode编码下
    $post_data['mobile'] = "$phone";
    $post_data['sendtime'] = ''; //不定时发送，值为0，定时发送，输入格式YYYYMMDDHHmmss的日期值
    
    $url='http://114.55.11.126:8888/sms.aspx?action=send';
    $o='';
    foreach ($post_data as $k=>$v)
    {
        $o.="$k=".urlencode($v).'&';
    }
    $post_data=substr($o,0,-1);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
    $result = curl_exec($ch);
    // var_dump($result);exit;

}
function makeArr($data,&$res,$id=0,$j=0){
    foreach($data as $v){
        if($v['pid']==$id){
            $temp=$v;
            // $temp['uid']=$temp['uid'];
            // $temp['u_name']=$temp['type_image'];
            // $temp['type_sort']=$temp['type_sort'];
            // $temp['pid']=$temp['pid'];
            $temp['i']=$j;
            $res[]=$temp;
            makeArr($data,$res,$v['uid'],$j+1);
        }
    }
 }
 //快递查询
//即时查询
 function find_express($code,$number)
{
    $post_data = array();
    $post_data["customer"] = '4450066D404F9E6632FEDDF24F023A55';
    $key= 'JvKEHsiy5109' ;
    $post_data["param"] = "{'com':'$code','num':'$number'}";

    $url='http://poll.kuaidi100.com/poll/query.do';
    $post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
    $post_data["sign"] = strtoupper($post_data["sign"]);
    $o="";
    foreach ($post_data as $k=>$v)
    {
        $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
    }
    $post_data=substr($o,0,-1);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $result = curl_exec($ch);

    $data = str_replace("\&quot;",'"',$result );

    $data = json_decode($data,true);


    return $data;
}

//智能查询
function finds()
{
    $url="http://www.kuaidi100.com/autonumber/auto?num=291646130143&key=JvKEHsiy5109";

    $result=curl_get($url);

    return $result;
}

 function curl_get($url)
{

    $info = curl_init();
    curl_setopt($info,CURLOPT_RETURNTRANSFER,true);
    curl_setopt($info,CURLOPT_HEADER,0);
    curl_setopt($info,CURLOPT_NOBODY,0);
    curl_setopt($info,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($info,CURLOPT_SSL_VERIFYPEER,false);
    curl_setopt($info,CURLOPT_SSL_VERIFYHOST,false);
    curl_setopt($info,CURLOPT_URL,$url);
    $output = curl_exec($info);
    curl_close($info);
    return $output;
}