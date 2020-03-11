<?php

namespace app\operate\model;

use think\Model;
use think\Request;

class Sever extends Model
{
    public function add_logs($datas)
    {
        $ip=Request::instance()->ip();
        $datas['ip']=$ip;
        $datas['time']=\date("Y-m-d H:i:s",\time());
        return db('sys_log')->insert($datas);
    }
}