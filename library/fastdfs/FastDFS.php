<?php

namespace app\library\fastdfs;
/**
 * 分布式文件上传
 * 
 */

class FastDFS
{

    public $servers = array(
        // array(
        //     'host' => '121.201.34.79',
        //     'port' => '22122',
        // ),
    );

    protected static $_client = null;

    public function __construct()
    {

    }

    public function init()
    {
        // $this->open();
    }

    public function open($group_name = '')
    {
        if (self::$_client === null) {
            if (extension_loaded('fastdfs_client')) {
                // 安装了FDFS客户端扩展
                self::$_client = new \FastDFS();
            } else {
                die('未安装FDFS客户端扩展');
                // 未安装FDFS客户端扩展
                //self::$_client = new \common\library\fastdfs\client\FastDFS();
            }
            $server = $this->servers[array_rand($this->servers)];
            $res = self::$_client->connect_server($server['host'], $server['port']);

            if ($res) {
                $this->tracker = $res;
            } else {
                $this->tracker = self::$_client->tracker_get_connection();
            }
            // $storage_info = $this->tracker->applyStorage($group_name);
            // print_r($this->tracker);exit;
            // 
            $this->storage = self::$_client->tracker_query_storage_store($group_name, $this->tracker);
        }
    }

    /**
     * 上传文件
     * @param string upload_filename 本地文件名
     * @param string file_ext 文件扩展名,不包括(.)符号
     * @param array  meta 文件元数据
     * @return array|string 成功返回文件信息数组,失败返回错误信息
     */
    public function uploadFile($group, $upload_filename, $file_ext = null, $meta = array())
    {
        $file_info = self::$_client->storage_upload_by_filename($upload_filename, $file_ext, $meta, $group, $this->tracker, $this->storage);
        // $file_info = self::$_client->storage_upload_by_filename($upload_filename);
        if (is_array($file_info)) {
            return $file_info;
        }
        return false;
    }

    /**
     * 上传文件,通过文件流
     * @param string file_buff 文件流
     * @param string file_ext 文件扩展名,不包括(.)符号
     * @param array  meta 文件元数据
     * @return array|string 成功返回文件信息数组,失败返回错误信息
     */
    public function uploadFileByBuff($group, $file_buff, $file_ext = null, $meta = array())
    {
        $file_info = self::$_client->storage_upload_by_filebuff($file_buff, $file_ext, $meta, $group, $this->tracker, $this->storage);
        if (is_array($file_info)) {
            return $file_info;
        }
        
        return self::$_client->get_last_error_info();
    }

    /**
     * 上传从属文件
     * @param string upload_filename 本地文件名
     * @param string master_filename 主文件名
     * @param string prefix 从文件后缀
     * @param string file_ext 文件扩展名
     * @param array meta 文件元数据
     * 
     * @return array|string 成功返回文件信息数组,失败返回错误信息
     */
    public function uploadSlaveFile($group, $upload_filename, $master_filename, $prefix, $file_ext = null, $meta = array())
    {
        if (empty($upload_filename) || empty($master_filename) || empty($prefix)) {
            return false;
        }
        $res = self::$_client->storage_upload_slave_by_filename($upload_filename, $group, $master_filename, $prefix, $file_ext, $meta, $this->tracker, $this->storage);
        if ($res) {
            return $res;
        } else {
            $error = self::$_client->get_last_error_info();
            return $error;
            // log
            // return false;
        }
    }

    /**
     * 上传从属文件,通过文件流
     * @param string master_filename 主文件名
     * @param string prefix 从文件后缀
     * @param string file_ext 文件扩展名
     * @param array meta 文件元数据
     * 
     * @return array|string 成功返回文件信息数组,失败返回错误信息
     */
    public function uploadSlaveFileByBuff($group, $file_buff, $master_filename, $prefix, $file_ext = null, $meta = array())
    {
        $file_info = self::$_client->storage_upload_slave_by_filebuff($file_buff, $group, $master_filename, $prefix,$file_ext, $meta, $this->tracker, $this->storage);
        if(is_array($file_info))
        {
            return $file_info;
        }
        // return self::$_client->get_last_error_info();
        return false;
    }

    /**
     *  获取文件地址 
     * 
     */
    public function getFileUrl($mfile,$size)
    {
        if(empty($mfile)) {
            return '';
        }

        return 'http://'.config_item('fastdfs_host').'/'.config_item('fastdfs_group').'/'.$mfile.'_'.$size.'.jpg?v='.time();
    }

    /**
     * 下载文件
     * @param string remote_filename 远程文件名
     * @param string upload_filename 本地文件名
     * 
     * @return bool 成功返回true失败返回false
     */
    public function downloadFile($group, $remote_filename, $upload_filename)  
    {
        return self::$_client->storage_download_file_to_file($group, $remote_filename, $upload_filename, 0, 0, $this->tracker, $this->storage);
    }

    /**
     * 下载文件到文件流
     * @param string remote_filename 远程文件名
     * 
     * @return string|false 成功返回文件流,失败返回false
     */
    public function downloadFileToBuff($group, $remote_filename)
    {
        $buff = self::$_client->storage_download_file_to_buff($group, $remote_filename, 0, 0, $this->tracker, $this->storage);
        if($buff !== false)
        {
            return $buff;
        }
        return false;
    }

    /**
     * 判断文件是否存在
     * 
     * @param   $groupName      文件组名
     * @param   $fileName       文件名
     *
     * @return  bool        文件存在返回true,不存在返回false;
     */
    public function fileExists($group = null, $name = null)
    {
        if(!$group || !$name)
            return false;
        $res = self::$_client->storage_file_exist($group, $name, $this->tracker, $this->storage);
        if($res) {
            return $res;
        }
        return false;
    }

    /**
     * 删除上传的文件
     * @param string filename 远程文件名
     * @return bool 成功返回true失败返回false
     */
    public function deleteFile($group, $filename)
    {
        if(!$group or !$filename)
        {
            return false;
        }
        $res = self::$_client->storage_delete_file($group, $filename, $this->tracker, $this->storage);
        if($res) {
            return $res;
        } else {
            // $error = self::$_client->get_last_error_info();
            // log
            return false;
        }
    }

    public function get_last_error_no()
    {
        return self::$_client->get_last_error_no();
    }

    public function get_last_error_info()
    {
        return self::$_client->get_last_error_info();
    }

    public function __destruct()
    {
        // self::$_client->disconnect_server($this->tracker);
    }
}