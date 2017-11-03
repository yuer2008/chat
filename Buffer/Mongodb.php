<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/2
 * Time: 14:27
 */
namespace app\Buffer;
class Mongodb{
    /*
    添加新的mongodb
    数据库链接
    */
    public static $mongodb=array(
                    'user'=>'mgo1hao',
                    'password'=>'kss2015',
                    'ip'=>'192.168.1.209',
                    'port'=>'27017',
                    'database'=>'xoxdb'
    );
    /*
    实例化对象
    对象放在instance里面
    */
    public static $instance = array();

    public static function instance($config_name){
        if(!isset(static::$$config_name)){
            echo "${$config_name} is not exist";
            throw new \Exception("${$config_name} is not exist");
        }

        if(empty(static::$instance[$config_name])){
            $user = static::$mongodb["user"];
            $password = static::$mongodb["password"];
            $ip = static::$mongodb["ip"];
            $port = static::$mongodb['port'];
            $database = static::$mongodb['database'];
            static::$instance[$config_name]=new \Mongo("mongodb://$user:$password@$ip:$port/$database");
        }
        return static::$instance[$config_name];
    }
}