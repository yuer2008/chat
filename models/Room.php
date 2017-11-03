<?php
namespace app\models;
use yii\db\ActiveRecord;
class Room extends ActiveRecord {

	public static function tableName() {
		return '{{%room}}';
	}
}
?>