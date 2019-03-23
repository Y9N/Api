<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Model\UserModel;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class ApiController extends Controller
{

    public function apitest()
    {
        $url="http://api.com/?type=23423423423";
        $client=new Client();
        $rs=$client->request('GET',$url);
        echo ($rs->getBody());
    }

    public function login(Request $request)
    {
        if(!request()->isMethod('post')){
            return view('login.login');
        }else{
            $name = $request->input('name');
            $pwd = $request->input('pwd');
            $where = [
                'name' => $name,
            ];
            $res =UserModel::where($where)->first();
            $mysqlpwd=$res['password'];
            $uid=$res['id'];
            $RES=password_verify($pwd,$mysqlpwd);
            if(password_verify($pwd,$mysqlpwd)){
                $token = substr(md5(time().mt_rand(1,99999)),10,10);
                setcookie('uid',$uid,time()+86400,'/','shop.com',false,true);
                setcookie('token',$token,time()+86400,'/','shop.com',false,true);
                Redis::set("str:uid:$uid",$token);
                $data=[
                    'code'=>0,
                    'msg'=>'ok'
                ];
                echo json_encode($data);
                //header("refresh:2;'http://shop.com/orderlist'");
            }else{
                $data=[
                    'code'=>10,
                    'msg'=>'Wrong account or password!'
                ];
                return $data;
            }
        }
    }

    public function api()
    {
        $name=$_POST['name'];
        $pwd=md5($_POST['pwd']);
        $info=UserModel::where('name',$name)->first();
        //print_r($info);die;
        if(!$info){
            $code=[
                'error'=>'10000',
                'msg'=>'Wrong account or password!'
            ];
        }else{
            if($pwd!=$info['password']){
                $code=[
                    'error'=>'10000',
                    'msg'=>'Wrong account or password!'
                ];
            }else{
                $token = substr(md5(time().mt_rand(1,99999)),10,10);
                $uid=$info['id'];
                Redis::del("token:one:$uid");
                Redis::set("token:one:$uid", $token);
                $code=[
                    'error'=>'0',
                    'msg'=>'ok',
                    'token'=>$token,
                    'uid'=>$uid,
                    'name'=>$info['name']
                ];
            }
        }
        return $code;
    }
}