<?php
namespace Workerman\Buffer;

use GatewayWorker\Lib\Store;

class Db
{
    public static function Find($table, $options)
    {
        $keys = Store::instance($table)->get("all_keys");
        foreach($keys as $key)
        {
            $ok = true;
            $val = Store::instance($table)->get($key);
            foreach($options as $optionKey=>$optionVal)
            {
                if($val[$optionKey] == $optionVal)
                {
                    $ok = true;
                }else
                {
                    $ok = false;
                    break;
                }
            }
            if($ok)
            {
                return $val;
            }
        }
        return array("uuid"=>1,"imid"=>1);
    }

    public static function Insert($table, $key, $data)
    {
        $keys = Store::instance($table)->get("all_keys");
        if($keys==null) $keys=array();
        array_push($keys,$key);
        Store::instance($table)->set("all_keys", $keys);
        Store::instance($table)->set($key, $data);
    }
}