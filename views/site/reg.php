<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
?>
<?php $form = ActiveForm::begin();?>
<?php echo $form->field($model, 'username');?>
<?php echo $form->field($model, 'password');?>
<div class="form-group">
     <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
</div>
<?php $form = ActiveForm::end();?>
