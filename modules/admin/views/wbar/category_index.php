<?php
use yii\widgets\ActiveForm;
?>
<div class="contentpanel">

    <div class="row">
        <?php
        $form = yii\widgets\ActiveForm::begin([
            'id' => 'add-form',
            'action' =>'index.php?r=admin/wbar/addcat',
            'method' =>'post',
            'enableAjaxValidation' => false,
            // 'enableAjaxValidation'   => true,
            // 'enableClientValidation' => false,
            'options' => ['class' => 'form-inline'],
        ]);
    ?>
            <div class="form-group">
                <label for="pt_name">添加分类</label>
                <input type="text" class="form-control" id="pt_name" name="pt_name" placeholder="分类名">
            </div>
                <input type="hidden" class="form-control" id="pid" name="pid" value="0">
            <button type="submit" class="btn btn-default">添加</button>
        <?php ActiveForm::end();?> 
    </div>
    <br />
    <div class="row">
        <div class="alert alert-info pastel alert-dismissable">
          <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
          <i class="fa fa-info pr10"></i>可拖动进行排序！
        </div>
    </div>
    <div class="row metro" style="margin-left:20px;">
        <div class="metro">
            <ul id="ztree" class="ztree" style="width:560px; overflow:auto;"></ul>    
        </div>
    </div>    
</div>

</div>
