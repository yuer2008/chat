<?php
/**
 * 网点管理
 */
namespace app\models;

use Yii;

class Shop extends \yii\db\ActiveRecord {
	//待审核
	const STATUS_UNAUDIT = 1;
	//已审核
	CONST STATUS_AUDIT = 2;
	// public $barea;
	// public $bcity;
	// public $shop_images;

	public static function tableName() {
		return '{{%netshop}}';
	}

	// 说明文字
	public static $labels = [
		self::STATUS_UNAUDIT => '待审核',
		self::STATUS_AUDIT => '已审核',
	];

	public function rules() {
		return [
			[['s_uid', 's_user_account', 's_shop_name'], 'required'],
			[['s_valid_code'], 'string', 'max' => 5],
			[['s_linkman'], 'string', 'max' => 50],
			[['s_tel_1', 's_email'], 'string', 'max' => 20],
			[['s_feedback'], 'string'],
			[['s_province_id', 's_city_id', 's_country_id', 's_town_id'], 'string', 'max' => 10],
			[['s_address', 's_img_1', 's_img_2', 's_img_3'], 'string', 'max' => 100],
			[['s_is_delete'], 'default', 'value' => 0],
			[['s_status'], 'default', 'value' => 1],
			[['s_addtime'], 'default', 'value' => time()],
			[['s_auditor', 's_audit_time'], 'safe'],

		];
	}

	/**
	 * 获得网点列表
	 * @param $params 参数
	 * @return 数据对象
	 */
	public static function getList($params, $orderBy = 's_addtime desc') {
		$cond = ['and', ['s_is_delete' => 0]];
		if (!empty($params['uid'])) {
			$cond[] = ['s_uid' => $params['uid']];
		}
		if (!empty($params['account'])) {
			$cond[] = ['s_user_account' => $params['account']];
		}
		if (!empty($params['shop_name'])) {
			$cond[] = ['s_shop_name' => $params['shop_name']];
		}
		if (!empty($params['linkman'])) {
			$cond[] = ['s_linkman' => $params['linkman']];
		}
		if (!empty($params['code'])) {
			$cond[] = ['s_valid_code' => $params['code']];
		}
		if (!empty($params['status'])) {
			$cond[] = ['s_status' => $params['status']];
		}
		if (!empty($params['address'])) {
			$cond[] = ['s_address' => $params['address']];
		}
		if (!empty($params['tel'])) {
			$cond[] = ['s_tel_1' => $params['tel']];
		}
		// print_r($cond);die;
		if (empty($cond)) {
			$data = self::find();
		} else {
			$data = self::find()->where($cond);
		}
		$data = $data->orderBy('s_addtime desc');
		return $data;
	}
}
?>