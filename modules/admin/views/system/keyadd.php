<?php
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>
<div class="container">
    
    
   

    <div class="row">
        <div class="panel">
              <div class="panel-heading">
                <span class="panel-title">敏感词添加</span>
                <a type="button" href="<?php Url::toRoute('system/keysku');?>" class="btn btn-info btn-gradient dark btn-inline">列表</a>
              </div>
              <div class="panel-body">
                <?php
                    $form = yii\widgets\ActiveForm::begin([
                        'id' => 'search-form',
                        'action' =>Url::toRoute('system/keyadd'),
                        'method' =>'post',
                        'enableAjaxValidation' => false,
                        // 'enableAjaxValidation'   => true,
                        // 'enableClientValidation' => false,
                        'options' => ['class' => 'form-horizontal'],
                    ]);
                ?>
                
                
                  <div class="form-group">
                    <label for="inputStandard" class="col-lg-3 control-label">敏感词</label>
                    <div class="col-lg-8">
                      <div class="bs-component1">
                        <input type="text" id="keyword" name="keyword" class="form-control" placeholder="key...">
                      </div>
                    </div>
                  </div>

                  <div class="form-group">
                    <div class="col-lg-6"></div>
                    <div class="col-lg-6">
                      
                        <button type="submit" class="btn btn-system btn-gradient dark btn-inline">添加</button>
                      
                    </div>
                  </div>
                <?php ActiveForm::end();?>
              </div>
            </div>
    </div>
</div>
<script type="text/javascript">
<?php $this->beginBlock('js_search_table_for_key') ?> 
    jQuery(document).ready(function() {
        //删除
        $('#search-form').submit(function(){
            var word = $('#keyword').val();
            if(word == ''){
                $('#keyword').attr('placeholder','不能为空');
                return false;
            }else{

            }
            
        });
  });
<?php $this->endBlock() ?>
</script>
  <?php $this->registerJs($this->blocks['js_search_table_for_key'], \yii\web\View::POS_END); ?>
