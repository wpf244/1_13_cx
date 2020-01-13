<?php
namespace app\admin\controller;

use app\admin\model\Goods as GoodsModel;

class Goods extends BaseAdmin
{
    private $model = "";
    public function _initialize(){
        parent::_initialize();
        $this->model = new GoodsModel();
    }
    
    public function type()
    {
        $list=db('goods_type')->order(['type_sort asc','type_id desc'])->paginate(10);
        $this->assign("list",$list);
        
        $page=$list->render();
        $this->assign("page",$page);
        
        return $this->fetch();
    }
    public function save_type(){
        $id=input('id');
        if($id){
            $data['type_name']=input('name');
            if(request()->file("image")){
                $data['type_image']=uploads('image');
            }
            if(request()->file("banner")){
                $data['type_banner']=uploads('banner');
            }
            $res=db('goods_type')->where("type_id",$id)->update($data);
            if($res){
                $this->success("修改成功");
            }else{
                $this->error("修改失败");
            }
        }else{
            $data['type_name']=input('name');
            if(request()->file("image")){
                $data['type_image']=uploads('image');
            }
            if(request()->file("banner")){
                $data['type_banner']=uploads('banner');
            }
            $re=db('goods_type')->insert($data);
            if($re){
                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
        }
    }
    public function modify(){
        $id=input('id');
        $re=db('goods_type')->where("type_id=$id")->find();
        
        echo json_encode($re);
    }
    public function delete_type(){
        $id=input('id');
       
        $del=db('goods_type')->where("type_id",$id)->delete();
        $res=db('goods')->where("fid=$id")->select();
        if($res){
            $dels=db('goods')->where("fid=$id")->delete();
            foreach ($res as $v){
                $rei=db('goods_img')->where("g_id={$v['gid']}")->select();
                if($rei){
                    $deli=db('goods_img')->where("g_id={$v['gid']}")->delete();
                }
            }
        }
        $this->redirect('type');
          
    }
    public function sorts(){
        $data=input('post.');
        
        foreach ($data as $id => $sort){
            db('goods_type')->where(array('type_id' => $id ))->setField('type_sort' , $sort);
        }
        $this->redirect('type');
    }
    public function change_type()
    {
        $id=\input("id");

        $re=db("goods_type")->where("type_id",$id)->find();

        if($re){
             if($re['type_recome'] == 1){
                db("goods_type")->where("type_id",$id)->setField("type_recome",0);
             }
             if($re['type_recome'] == 0){
                db("goods_type")->where("type_id",$id)->setField("type_recome",1);
             }
             echo '0';
        }else{
            echo '1';
        }
    }
    public function lister()
    {
        $map=[];

        $title=\input("title");

        if($title){
            $map['name|code']=["like","%".$title."%"];
        }else{
            
            $title='';
        }
        $this->assign("title",$title);
        $list=db('goods')->alias('a')->where($map)->join("goods_type b","a.fid = b.type_id")->order(['sort'=> 'asc','id'=>'desc'])->paginate(20,false,['query'=>request()->param()]);
        $this->assign("list",$list);
        
        $page=$list->render();
        $this->assign("page",$page);


        return $this->fetch();
    }
    public function add(){
        $res=db('goods_type')->select();
        $this->assign("res",$res);


        return $this->fetch();
    }
    public function save(){
        $data=input('post.');
        if(request()->file("image")){
            $data['image']=uploads('image');

        }
       
        if(input('status')){
            $data['status']=1;
        }
        if(input('up')){
            $data['up']=1;
        }
        $re=db("goods")->insert($data);
        if($re){
            $this->success("添加成功",url("lister"));
        }else{
            $this->error("添加失败");
        }
    }
    public function changes(){
        $id=input('id');
        $re=db('goods')->where("id",$id)->find();
        if($re){
            if($re['status'] == 0){
                $res=db('goods')->where("id",$id)->setField("status",1);
            }
            if($re['status'] == 1){
                $res=db('goods')->where("id",$id)->setField("status",0);
                
            }
            echo '0';
        }else{
            echo '1';
        }
    }
    public function changeu(){
        $id=input('id');
        $re=db('goods')->where("id",$id)->find();
        if($re){
            if($re['up'] == 0){
                $res=db('goods')->where("id",$id)->setField("up",1);
            }
            if($re['up'] == 1){
                $res=db('goods')->where("id",$id)->setField("up",0);
    
            }
            echo '0';
        }else{
            echo '1';
        }
    }
    public function sort(){
        $data=input('post.');
    
        foreach ($data as $id => $sort){
            db('goods')->where(array('id' => $id ))->setField('sort' , $sort);
        }
        $this->redirect('lister');
    }
    public function modifys(){
        $id=input('id');
        $re=db('goods')->where("id",$id)->find();
        $this->assign("re",$re);
        
        $res=db('goods_type')->select();
        $this->assign("res",$res);


        
        return $this->fetch();
    }
    public function usave(){
        $id=input('id');
        $data=input('post.');
        $re=db("goods")->where("id",$id)->find();

        if($re){
            if(request()->file("image")){
                $data['image']=uploads('image');
              
            }else{
                $data['image']=$re['image'];
            }
        
            if(input('status')){
                $data['status']=1;
            }else{
                $data['status']=0;
            }
            if(input('up')){
                $data['up']=1;
            }else{
                $data['up']=0;
            }
            
            $res=db("goods")->where("id",$id)->update($data);
            if($res){
                $this->success("修改成功",url('lister'));
            }else{
                $this->error("修改失败");
            }
        }else{
            $this->error("参数错误");
        }
        
    }
    public function delete(){
        $id=input('id');
        $del=$this->model->deleteGoods($id);
        $this->redirect('lister');
    }
    public function delete_all(){
        $id=input('id');
        $arr=explode(",", $id);
        $del=$this->model->deleteAll($arr);
        $this->redirect('lister');
    }
    public function spec(){
        $id=input('id');
        $list=db('goods_spec')->alias('a')->where("g_id=$id")->join('goods b','a.g_id=b.id')->paginate(10);
        $this->assign("list",$list);
        
        $page=$list->render();
        $this->assign("page",$page);
        
        $this->assign("gid",$id);
        
        return $this->fetch();
    }
    public function s_save(){
        $id=input('sid');
        if($id){
            $data=input('post.');
            $re=db('goods_spec')->where("sid=$id")->find();
            if(!is_string(input('s_image'))){
                $data['s_image']=uploads("s_image");
            }else{
                $data['s_image']=$re['s_image'];
            }
            if(input('s_status')){
                $data['s_status']=1;
            }else{
                $data['s_status']=$re['s_status'];
            }
            $res=$this->model->updateSpec($id,$data);
            if($res){
                $this->success("修改成功！");
            }else{
                $this->error("修改失败！");
            }
        }else{
            $data=input('post.');
            if(!is_string(input('s_image'))){
                $data['s_image']=uploads("s_image");
            }
            if(input('s_status')){
                $data['s_status']=1;
            }
            
            $re=$this->model->addSpec($data);
            if($re){
                $this->success("添加成功！");
            }else{
                $this->error("添加失败！");
            }
        }
         
    }
    public function change_s(){
        $id=input('id');
        $re=db('goods_spec')->where("sid=$id")->find();
        if($re){
            if($re['s_status'] == 0){
                $res=db('goods_spec')->where("sid=$id")->setField("s_status",1);
            }
            if($re['s_status'] == 1){
                $res=db('goods_spec')->where("sid=$id")->setField("s_status",0);
    
            }
            echo '0';
        }else{
            echo '1';
        }
    }
    public function modify_s(){
        $id=input('id');
        $re=db('goods_spec')->where("sid=$id")->find();
        
        echo json_encode($re);
    }
    public function delete_s(){
        $id=input('id');
        $re=db('goods_spec')->where("sid=$id")->find();
        if($re){
            $del=db('goods_spec')->where("sid=$id")->delete();

            $cars=db("cars")->where("sid",$id)->find();
                if($cars){
                    db("cars")->where("sid",$id)->delete();
                }

            if($del){
                echo '0';
            }else{
                echo '1';
            }
        }else{
            echo '1';
        }
    }
    public function img(){
        $id=input('id');
        $list=db('goods_img')->field("a.*,b.name")->alias('a')->where("g_id=$id")->join('goods b','a.g_id=b.id')->paginate(10);
        $this->assign("list",$list);
        
        $page=$list->render();
        $this->assign("page",$page);
        
        $this->assign("gid",$id);
        
        return $this->fetch();
    }
    public function i_save(){
        $id=input('id');
        if($id){
            $data=input('post.');
            $re=db('goods_img')->where("id=$id")->find();
            if(!is_string(input('image'))){
                $data['image']=uploads("image");
           
            }else{
                $data['image']=$re['image'];
            }
            if(input('i_status')){
                $data['i_status']=1;
            }else{
                $data['i_status']=$re['i_status'];
            }
            $res=$this->model->updateImg($id,$data);
            if($res){
                $this->success("修改成功！");
            }else{
                $this->error("修改失败！");
            }
        }else{
            $data=input('post.');
            if(!is_string(input('image'))){
                $data['image']=uploads("image");
          
            }
            if(input('i_status')){
                $data['i_status']=1;
            }
    
            $re=$this->model->addImg($data);
            if($re){
                $this->success("添加成功！");
            }else{
                $this->error("添加失败！");
            }
        }
         
    }
    public function change_i(){
        $id=input('id');
        $re=db('goods_img')->where("id=$id")->find();
        if($re){
            if($re['i_status'] == 0){
                $res=db('goods_img')->where("id=$id")->setField("i_status",1);
            }
            if($re['i_status'] == 1){
                $res=db('goods_img')->where("id=$id")->setField("i_status",0);
    
            }
            echo '0';
        }else{
            echo '1';
        }
    }
    public function modify_i(){
        $id=input('id');
        $re=db('goods_img')->where("id=$id")->find();
    
        echo json_encode($re);
    }
    public function delete_i(){
        $id=input('id');
        $re=db('goods_img')->where("id=$id")->find();
        if($re){
            $del=db('goods_img')->where("id=$id")->delete();
            if($del){
                echo '0';
            }else{
                echo '1';
            }
        }else{
            echo '1';
        }
    }
    public function shop()
    {
        $list=db("goods_shop")->order("sid desc")->select();

        $this->assign("list",$list);
        
        return $this->fetch();
    }
    public function save_t(){
        $id=input('sid');
        $data=input("post.");
        if($id){
           
            $res=db('goods_shop')->where("sid",$id)->update($data);
            if($res){
                $this->success("修改成功");
            }else{
                $this->error("修改失败");
            }
        }else{
           
            $re=db('goods_shop')->insert($data);
            if($re){
                $this->success("保存成功");
            }else{
                $this->error("保存失败");
            }
        }
    }
    public function modify_t(){
        $id=input('id');
        $re=db('goods_shop')->where("sid=$id")->find();
        
        echo json_encode($re);
    }
    public function delete_t(){
        $id=input('id');
       
        $del=db('goods_shop')->where("sid",$id)->delete();
        
        $this->redirect('shop');
          
    }

    
}