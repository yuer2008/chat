<?php
namespace app\library\fastdfs\client;
require(__DIR__ . '/../FastDFS.php');
class api{
    /**
     * FastDFS上传文件
     * @param unknown $fileurl 上传文件
     * @param unknown $sizes 附属文件
     * @return string
     */
            public static  function applydfs($fileurl,$sizes=array()){ 

                 $dfs=new \FastDFS();
                 $tracker = $dfs->tracker_get_connection();
                 $location = "";
                 if($dfs->active_test($tracker)){
                     $storaged = $dfs->tracker_query_storage_store("group1",$tracker);
                     if(!empty($sizes)){
                         $count = 0;
                         $filename = $dfs->storage_upload_by_filename($fileurl);
                         if(isset($filename['group_name'])&&isset($filename['filename'])){
                             $location =$filename['group_name']."/".$filename['filename'];
                         }
                         foreach($sizes as $key=>$val){
                             $snapshot_file_info =$dfs->storage_upload_slave_by_filename($val,"group1",$filename['filename'],$key);
                             if($snapshot_file_info){
                                 $count++;
                             }
                         }
                         if($count <> count($sizes)){
                             $location = "";
                         }
                     }else{
                         $filename = $dfs->storage_upload_by_filename($fileurl);
                         if(isset($filename['group_name'])&&isset($filename['filename'])){
                             $location =$filename['group_name']."/".$filename['filename'];
                         }
                     }
                 }
                 return $location;
             }
}
?>
