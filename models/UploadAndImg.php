<?php
/**
 *+------------------------------------------------------------------------------
 * 文件上传类和图片处理公用类
 *+------------------------------------------------------------------------------
 * @author    霄 <277130180@qq.com>
 * @version   1.0
 *+------------------------------------------------------------------------------
 */
namespace app\models;
use yii\base\Model;

class UploadAndImg extends Model {
	/**
	 * 图片或者文件上传
	 * @param  [string] $targetFolder 文件基于web根目录保存的位置如:"upload/avatar/"
	 * @param  [string] $fileName 上传表单,文件上传文本框的名字
	 * @param  [string] $size 上传文件大小M表示,如"1M"
	 * @return 上传成功返回如下数组:
	 *[
	 *'showImgName' => $targetFolder . $newFileName,          //返回"upload/avatar/1111_1.jpg"
	 *'baseName' => $baseName,         //返回"1111_1"
	 *'imgName' => $newFileName,         //返回"1111_1.jpg"
	 *'extension' => $fileParts['extension'],         //返回"jpg"
	 *];
	 *
	 * 失败为false
	 */
	public static function upload($targetFolder, $fileName, $size = '1M', $fileTypes = ['jpg', 'jpeg', 'gif', 'png']) {
		if (!empty($_FILES)) {
			$tempFile = $_FILES[$fileName]['tmp_name'];
			if (intval($size) * 1024 * 1024 < $_FILES[$fileName]['size']) {
				return false; //超过了限制大小
			}
			$targetPath = $targetFolder . date('Ym');
			if (!file_exists($targetPath)) {
				mkdir($targetPath, 0777, true);
			}
			$fileParts = pathinfo($_FILES[$fileName]['name']);
			if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
				$baseName = time() . '_' . rand(1, 9999);
				$newFileName = $baseName . '.' . strtolower($fileParts['extension']);
				$targetFile = $targetPath . '/' . $newFileName;
				if (move_uploaded_file($tempFile, $targetFile)) {
					$imgInfo = getimagesize($targetFile);
					$w = $imgInfo[0];
					$h = $imgInfo[1];
					return [
						'showImgName' => $targetFile,
						'baseName' => $baseName,
						'ym' => date('Ym'), //年月目录
						'imgName' => $newFileName,
						'w' => $w,
						'h' => $h,
						'extension' => $fileParts['extension'],
					];
				} else {
					return false;
				}
			} else {
				return false;
			}
		}
	}

}
