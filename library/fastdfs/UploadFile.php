<?php
/**
 *+------------------------------------------------------------------------------
 * 文件上传类
 * */
namespace app\library\fastdfs;
use Yii;
class UploadFile{
	public static function upload_dfs($group='group1',$fileName="Filename", $size = '1M') {
		if (!empty($_FILES)) {
			$tempFile = $_FILES[$fileName]['tmp_name'];
			if (intval($size) * 1024 * 1024 < $_FILES[$fileName]['size']) {
				return false; //超过了限制大小
			}
			
			$fileParts = pathinfo($_FILES[$fileName]['name']);

			$model = Yii::$app->fastdfs;
			$model->open();
				
			if ($rs = $model->uploadFile($group, $_FILES[$fileName]['tmp_name'], $fileParts['extension'])) {
				return [
					'group_name'=> $rs['group_name'],
    					'filename' => $rs['filename']
				];
			} else {
				return false;
			}
		}
	}
}
