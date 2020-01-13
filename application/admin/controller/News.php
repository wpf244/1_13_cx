<?php
namespace app\admin\controller;

use app\admin\model\News as NewsModel;
class News extends BaseAdmin{
    private $model = "";
    public function _initialize(){
        parent::_initialize();
        $this->model = new NewsModel;
    }
    public function type(){
        $list = Db('news_type')->order('ty_id desc')->paginate(10);
        $this->assign("list",$list);
        return view('type');
    }
    public function save_type(){
        if($this->request->isAjax()){
            $id=$_POST['id'];
            if($id){
                $data['ty_name']=$_POST['name'];
                $res=$this->model->updateNewstype("ty_id=$id",$data);
                if($res){
                    $this->success("修改成功！",url('type'));
                }else{
                    $this->error("修改失败！",url('type'));
                }
            }else{
                $data['ty_name']=$_POST['name'];
                $re=$this->model->addNewstype($data);
                if($re){
                    $this->success("添加成功！",url('type'));
                }else{
                    $this->error("添加失败！",url('type'));
                }
            }
    
        }else{
            $this->success("非法提交",url('type'));
        }
    }
    function delete_type(){
        $id=input('id');
        $del=$this->model->deleteType($id);
        $this->redirect('type');
    }
    public function modify(){
        $id=input('id');
        $re=db('news_type')->where("ty_id=$id")->find();
        echo json_encode($re);
    }
    public function lister(){
        //新闻分类
        $type = db('news_type')->select();
        $this->assign('type',$type);

        $time = input('date-range-picker');
        $start = strtotime(substr("$time",0,10));
        $end = strtotime(substr("$time",13,10));
 
        $fid = input('fid');
        
        $title = input('title');
        
        if($fid || $title || $time){
             if($time){
                 //dump($start);
                 //dump($end);die;
                 $map['time'] = array(array('gt',$start),array('lt',$end));
                 //$map['time'] =array('between', "$start,$end");
             }
            if($fid){
                $map['fid'] = array('like','%'.$fid.'$fid');
            }
            if($title){
                $map['title'] = array('like','%'.$title.'%');
            }
        }else{
            $time = "";
            $fid = "";
            $title = "";
            $map = [];
        }
        $this->assign('time',$time);
        $this->assign('fid',$fid);
        $this->assign('title',$title);

        $list = db('news')->alias('a')->join("news_type b","a.fid = b.ty_id")->where($map)->order(['sort'=>'asc','id'=>'desc'])->paginate(10,false,['query'=>request()->param()]);
        // print_r($list);
        //$list = db('news')->alias('a')->join("news_type b","a.fid = b.ty_id")->order(['sort'=>'asc','id'=>'desc'])->paginate(10);
        $this->assign("list",$list);
        $page=$list->render();
        $this->assign("page",$page);
        return view('lister');
    }
    public function add(){
        $res=db('news_type')->select();
        $this->assign("res",$res);
        
        return view('add');
    }
    public function save(){
        $data=input("post.");
        $image=request()->file("image");

        if($image){
            $data['image']=uploads('image');
        }
        $data['time']=time();
       
        $re=$this->model->addNews($data);
        if($re){
            $this->success("添加成功！",url('lister'));
        }else{
            $this->error("修改失败！");
        }
    }
    public function change(){
        $id=$_POST['id'];
        $res=$this->model->updateStatus($id);
        if($res){
            echo '1';
        }else{
            echo '0';
        }
    }
    public function changes(){
        $id=$_POST['id'];
        $res=$this->model->updateGroom($id);
        if($res){
            echo '1';
        }else{
            echo '0';
        }
    }
    public function modifys(){
        $res=db('news_type')->select();
        $this->assign("res",$res);
        $id=input('id');
        $re=db('news')->where("id=$id")->find();
        $this->assign("re",$re);
        return view('modifys');
    }
    public function usave(){
        $id=input('id');
        $re=db('news')->where("id=$id")->find();
        $data=input("post.");
        $image=request()->file("image");

        if($image){
            $data['image']=uploads('image');
        }else{
            $data['image']=$re['image'];
        }
        $res=$this->model->updateNews("id=$id", $data);
        if($res){
            $this->success("修改成功！",url('lister'));
        }else{
            $this->error("修改失败！");
        }
    }
    public function delete(){
        $id=input('id');
        $del=$this->model->deleteNews($id);
        $this->redirect('lister');
    }
    public function sort(){
        $data=input('post.');
        $news=db('news');
        foreach ($data as $id => $sort){
            $news->where(array('id' => $id ))->setField('sort' , $sort);
        }
        $this->redirect('lister');
    }
    public function delete_all(){
        $id=input('id');
        $arr=explode(",", $id);
        $del=$this->model->deleteAll($arr);
        $this->redirect('lister');
    }
    
    
    
    
    
    
    
    
    
    
}