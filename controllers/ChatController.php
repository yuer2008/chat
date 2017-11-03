<?php
namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\models\Room;
use yii\data\Pagination;
class ChatController extends Controller{
	public   $layout  = "chat";
	public function init(){
		$view = Yii::$app->view;
        $uid = !empty(Yii::$app->user->getIdentity())? Yii::$app->user->getIdentity()->id:'';
        $view->params['uid'] = $uid;
        if($uid){
            $view->params['username'] = Yii::$app->user->getIdentity()->username;    
        }
	}
	public function actionOne(){
		Yii::$app->view->title = "chat room";
		$uid = @Yii::$app->user->getIdentity()->id;
		$query = Room::find();
		$pages = new Pagination(['totalCount' => $query->count(), 'pageSize' => 10]);
		$room = $query->offset($pages->offset)->limit($pages->limit)->all();
		
		return $this->render('one', [
			'room' => $room,
			'page' => $pages,
			'uid' => $uid,
		]);
	}
	
}
?>