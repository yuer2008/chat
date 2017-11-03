<?php
namespace app\modules\admin\models;

use Yii;

class Keyword extends \yii\mongodb\ActiveRecord{

	/**
     * @inheritdoc
     */
    public static function collectionName()
    {
        return 'keyword';
    }

    public function attributes(){
        return ['_id', 'word_name'];
    }

    /**
	* 返回所有过滤字符数组
	* @return array
    */
	public static function getAll(){
		$filter_keys_list = Yii::$app->redis->get('filter_keys_list');
		if(empty($filter_keys_list)){
			$collection = Yii::$app->mongodb->getCollection(self::collectionName());
			$info = $collection->find();
			$info = self::find()->asArray()->all();
			$data = [];
			if(is_array($info)){
				foreach ($info as $key => $value) {
					$data[$value['word_name']] = '***';//str_pad('',strlen($value['word_name']),'*');
				}
				Yii::$app->redis->setex('filter_keys_list', 10, json_encode($data));
				return $data;
			}
		}
		return json_decode($filter_keys_list, true);
	}
	//列表，管理后台
	public function getAllList($param=[]){
		$cont = [];
		if(!empty($param['keyword'])){
            $cont = ['like','word_name',$param['keyword']];
        }

		
		$info = self::find()->where($cont);
		// $info = self::find()->asArray()->all();
		return $info;
		
	}

	/**
	* 返回过滤后的字符
	* @param 需要过滤的字符串
	* @return string 过滤后的字符串
    */
	public static function filterWord($str){
		$keyList = self::getAll();
		
		// print_r($keyList);

		if(empty($keyList) or !is_array($keyList)){
			return $str;
		}
		$replacedString = strtr($str, $keyList);
        return $replacedString;
	}
}
?>