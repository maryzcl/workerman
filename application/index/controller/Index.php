<?php
namespace app\index\controller;
use think\Db;
use think\Controller;
use think\exception\Handle;


class Index extends Controller
{
    public function index()
    {
       echo phpinfo();exit;
        //echo phpinfo();exit;
        $from_id=input('from_id');
        $to_id=input('to_id');
        $this->assign('from_id',$from_id);
        $this->assign('to_id',$to_id);
        return view();
    }
    public function lists()
    {
        $from_id=input('from_id');
        $this->assign('from_id',$from_id);
        return view();
    }
    public function test($dir)
    {

           $files=[];
           if(@$handle=opendir($dir)){
               while(($file=readdir($handle))!==false)
               {
                   if($file!='.' && $file!='..')
                   {
                       if(is_dir($dir.'/'.$file))
                       {
                           $files[$file]=$this->test($dir.'/'.$file);
                       }
                       else
                       {
                           $files[]=$file;
                       }
                   }
               }
               closedir($handle);
               return $files;
           }



    }
    public function infiniteTree($data,$pid){
        if (!is_array($data) || empty($data) ) return false;
        $tree = array();
        foreach ($data as $k => $v) {
            if ($v['pid'] == $pid) {
                $v['sub'] = $this->infiniteTree($data,$v['id']);
                $tree[] = $v;
                unset($data[$k]);
            }
        }
        return $tree;
    }
  
    public function my_dir($dir)
    {
       $files = [];
        if(@$handle = opendir($dir)) {
            while(($file = readdir($handle)) !== false) {
                if($file != ".." && $file != ".") {
                    if(is_dir($dir . "/" . $file)) { //如果是子文件夹，进行递归
                        $files[$file] =$this->my_dir($dir . "/" . $file);
                    } else {
                        $files[] = $file;
                    }
                }
            }
            closedir($handle);
        }
        return $files;

    }
    public function userFid($uid = 10){
        static $uid_array=[];
        $list = Db::name('concact')->where(['parent_id'=>$uid])->field('uid,parent_id')->select();
        foreach ($list as $k=>$v){
            //dump("123");
                $uid_array[] = $v['uid'];
                $this->userFid($v['uid']);//调取本身
        }
        return $uid_array;
    }
    //tcp服务器
    public function test_1()
    {
        return view();exit;
        $server=new \swoole_server('0.0.0.0','9501');
        $server->on('connect',function($server,$fd){
            echo "建立连接 \n";

        });
        $server->on('receive',function ($server,$fd,$from_id,$data){
            echo "接受数据\n";
            var_dump($data);
            var_dump($fd,$from_id);

        });
        $server->on('close',function($server,$fd){
            echo "连接关闭";

        });
       $server->start();

    }
    public function test_2()
    {
        $server=new \swoole_server('0.0.0.0','9502',SWOOLE_PROCESS,SWOOLE_SOCK_UDP);
        $server->on('packet',function($serve,$data,$fd){
            $serve->sendto($fd['address'],$fd['port'],"Server:$data");
            var_dump($fd);

        });
        $server->start();


    }
   public function test_3()
   {
       $server=new \swoole_http_server('0.0.0.0','9503');
       $server->on('request',function($request,$response){
           var_dump($request);
           $response->header('Content-Type:',"text/html;charset=utf-8");
           $response->end('hello world'.rand(1,1000));



       });
       $server->start();
   }
   public function test_4()
   {
       $server=new \swoole_websocket_server('0.0.0.0','9503');
       $server->on('open',function ($ws,$request){
           $ws->push($request->fd,"welcome to".PHP_EOL);

       });
       $server->on('message',function($ws,$request){
           echo "message:$request->data";
           $ws->push($request->fd,'get it message');

       });
       $server->on('close',function($ws,$request){
           echo "关闭连接\n";
       });
       $server->start();

   }
   public function test_5()
   {
       swoole_timer_tick(1000,function($timer_id){
          echo "循环每秒执行:".$timer_id.PHP_EOL;

      });
      swoole_timer_after(3000,function(){
         echo "3000后执行:".PHP_EOL;
      });
   }
   public function test_6()
   {
       swoole_async_read('/test.txt',function($file_name,$content){
           echo "$file_name  $content";

       });
   }
  public function test_7()
  {
      $content='hello world';
      swoole_async_writefile('/test1.txt',$content,function($filename){
          echo "$filename";

      },0);
  }
  public function test_8()
  {
     $db=new \swoole_mysql();
     $config=[
         'host'=>'192.168.1.7',
         'user'=>'test',
         'password'=>'123456',
         'database'=>'test',
         'charset'=>'utf8'
     ];
     $db->connect($config,function($db,$str){
         if($str===false)
         {
             var_dump($db->connect_errno,$db->connect_error);
             die('连接失败');
         }
         $sql='show tables';
         $db->query($sql,function(\swoole_mySQL $db,$r){
             if($r===false)
             {
                 var_dump($db->error);
                 die('操作失败');
             }
             elseif ($r===true)
             {
                 var_dump($db->affected_rows,$db->insert_id);
             }
             var_dump($r);
             $db->close();


         });


     });
  }


}
