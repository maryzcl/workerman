<?php
namespace app\api\controller;
use think\Db;
use think\Controller;
class Chat extends Controller{
    public function save_message()
    {
        if($this->request->isAjax())
        {
            $message=input('post.');
            $data['fromid']=$message['from_id'];
            $data['fromname']=$this->getName($data['fromid']);
            $data['toid']=$message['to_id'];
            $data['toname']=$this->getName($data['toid']);
            $data['content']=$message['data'];
            $data['time']=$message['time'];
            $data['isread']=0;
            $data['type']=1;
            Db::name('chat_communication')->insert($data);


        }
    }
    public function getName($uid)
    {
       $nickname= Db::name('chat_user')->where(['id'=>$uid])->value('nickname');
       return $nickname;
    }
    public function get_head_img()
    {
        if($this->request->isAjax())
        {
            $from_id=input('from_id');
            $to_id=input('to_id');
            $from_img= Db::name('chat_user')->where(['id'=>$from_id])->value('headimgurl');
            $to_img= Db::name('chat_user')->where(['id'=>$to_id])->value('headimgurl');
            return ['from_head_img'=>$from_img,'to_head_img'=>$to_img];
        }

    }
    public function get_user_name()
    {
        if($this->request->isAjax())
        {
            $to_id=input('to_id');
            $nickname= Db::name('chat_user')->where(['id'=>$to_id])->value('nickname');
            return ['nickname'=>$nickname];
        }

    }
    public function get_chat_data()
    {
        if($this->request->isAjax())
        {
            $from_id=input('from_id');
            $to_id=input('to_id');
            $message= Db::name('chat_communication')->where('(fromid='.$from_id.' and toid='.$to_id.') or (fromid='.$to_id.' and toid='.$from_id.')')->select();
            return $message;
        }
    }
    public function upload_img()
    {
        if($this->request->isAjax())
        {
            $file=$_FILES['file'];
            $fromid=input('from_id');
            $toid=input('to_id');
            $online=input('online');
            $suffix=strtolower(strrchr($file['name'],'.'));
            $type=['.jpg','.png','.jpeg','.gif'];
            if(!in_array($suffix,$type))
            {
                return ['status'=>'img type error'];
            }
            if($file['size']/1024>100)
            {
                return ['status'=>'img is too big'];
            }
            $file_name=uniqid('chat_img_',false);
            $upload_path=ROOT_PATH.'/public/uploads/';
            $file_up=$upload_path.$file_name.$suffix;
            $re=move_uploaded_file($file['tmp_name'],$file_up);
            if($re)
            {
                $name=$file_name.$suffix;
                $data['content']=$name;
                $data['fromid']=$fromid;
                $data['toid']=$toid;
                $data['isread']=0;
                $data['fromname']=$this->getName($data['fromid']);
                $data['toname']=$this->getName($data['toid']);
                $data['time']=time();
                $data['type']=2;
                $row=Db::table('chat_communication')->insertGetId($data);
                if($row)
                {
                    return ['status'=>'ok','img_name'=>$name];
                }
                else
                {
                    return ['status'=>'false'];
                }


            }
        }
    }
    public function get_list()
    {
        if($this->request->isAjax())
        {
            $from_id=input('id');
            $list=Db::name('chat_communication')->field('id,type,fromid,fromname,toid,time,isread')->where(['toid'=>$from_id])->group('fromid')->select();
            $row=array_map(function($v){
                $info=$this->get_last_message($v['fromid'],$v['toid']);
                return [
                    'head_img'=>$this->get_one_img($v['fromid']),
                    'fromname'=>$v['fromname'],
                    'not_read_count'=>$this->get_not_read($v['fromid'],$v['toid']),
                    'last_message'=>$info['content'],
                    'time'=>date("Y-m-d",$info['time']),
                    'page_url'=>'http://192.168.1.7?from_id='.$v['toid'].'&to_id='.$v['fromid'],
                    'type'=>$info['type'],
                    'isread'=>$v['isread'],

                ];
            },$list);
            return $row;
        }
    }
    public function get_one_img($uid)
    {
      $head_img_url=Db::name('chat_user')->where(['id'=>$uid])->value('headimgurl');
      return $head_img_url;
    }
    public function get_not_read($from_id,$to_id)
    {
        $count=Db::name('chat_communication')->where(['fromid'=>$from_id,'toid'=>$to_id,'isread'=>0])->count();
        return $count;

    }
    public function get_last_message($from_id,$to_id)
    {
        $last_message=Db::name('chat_communication')->where('(fromid='.$from_id.' and toid='.$to_id.') or (fromid='.$to_id.' and toid='.$from_id.')')->order('id desc')->limit(1)->find();
        return $last_message;

    }
    public function change_not_read()
    {
        if($this->request->isAjax())
        {
            $from_id=input('to_id');
            $to_id=input('from_id');
            Db::name('chat_communication')->where(['fromid'=>$from_id,'toid'=>$to_id,'isread'=>0])->update(['isread'=>1]);
        }
    }


}