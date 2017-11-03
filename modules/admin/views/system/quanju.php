<?php
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>
<div class="container">
    
    
    <div class="panel">
  <div class="panel-heading">
    <ul class="nav panel-tabs-border panel-tabs" style="left:10px">
      <li class="<?php if($tabs=='quanju')echo 'active';?>">
        <a href="<?php echo Url::toRoute('system/quanju');?>" data-toggle="tab">全局设置</a>
      </li>
      <li class="<?php if($tabs=='upsets')echo 'active';?>">
        <a href="<?php echo  Url::toRoute('system/upsets');?>" data-toggle="tab">上传设置</a>
      </li>
    </ul>
  </div>
  <div class="panel-body">
    <div class="tab-content pn br-n">
        <div id="tab1_1" class="tab-pane active" style="display:<?php echo $tabs=='quanju'?'':'none';?>">
            <div class="panel-body">
                <?php
                    $form = yii\widgets\ActiveForm::begin([
                        'id' => 'quanju-form',
                        'action' =>Url::toRoute('system/quanju'),
                        'method' =>'post',
                        'enableAjaxValidation' => false,
                        'options' => ['class' => 'form-horizontal'],
                    ]);
                ?>
                
                  
                    <div class="form-group">
                        <label for="website_name" class="col-lg-3 control-label">网站名称</label>
                        <div class="col-lg-8">
                            <input type="text" id="website_name" name="website_name" class="form-control" value="<?php echo @$model['website_name']?>" placeholder="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="website_version" class="col-lg-3 control-label">网站版本</label>
                        <div class="col-lg-8">
                            <input type="text" id="website_version" name="website_version" value="<?php echo @$model['website_version']?>" class="form-control" placeholder="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="icp" class="col-lg-3 control-label">ICP备案号</label>
                        <div class="col-lg-8">
                            <input type="text" id="icp" name="icp" class="form-control" value="<?php echo @$model['icp']?>" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="com_tel" class="col-lg-3 control-label">公司电话</label>
                        <div class="col-lg-8">
                            <input type="tel" id="com_tel" name="com_tel" value="<?php echo @$model['com_tel']?>" class="form-control" placeholder="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="com_fax" class="col-lg-3 control-label">公司传真</label>
                        <div class="col-lg-8">
                            <input type="text" id="com_fax" name="com_fax" value="<?php echo @$model['com_fax']?>" class="form-control" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="com_email" class="col-lg-3 control-label">公司邮箱</label>
                        <div class="col-lg-8">
                            <input type="email" id="com_email" name="com_email" value="<?php echo @$model['com_email']?>" class="form-control" placeholder="">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="com_address" class="col-lg-3 control-label">公司地址</label>
                        <div class="col-lg-8">
                            <input type="text" id="com_address" name="com_address" value="<?php echo @$model['com_address']?>" class="form-control" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="zipcode" class="col-lg-3 control-label">邮编</label>
                        <div class="col-lg-8">
                            <input type="text" id="zipcode" name="zipcode" value="<?php echo @$model['zipcode']?>" class="form-control" placeholder="">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="inputStandard" class="col-lg-3 control-label"></label>
                        <div class="col-lg-8">
                            <input type="hidden"  name="_id" value="<?php echo @$_id?>" class="form-control" placeholder="">
                            <button type="submit" class="btn btn-system btn-gradient dark btn-inline">更新</button>
                        </div>
                    </div>

                <?php ActiveForm::end();?>
              </div>

            </div>
        </div>
      <div id="tab1_2" class="tab-pane" style="display:<?php echo $tabs=='upsets'?'':'none';?>">
        <div class="panel-body">
                <?php
                    $form = yii\widgets\ActiveForm::begin([
                        'id' => 'upsets-form',
                        'action' =>Url::toRoute('system/upsets'),
                        'method' =>'post',
                        'enableAjaxValidation' => false,
                        // 'enableAjaxValidation'   => true,
                        // 'enableClientValidation' => false,
                        'options' => ['class' => 'form-horizontal'],
                    ]);
                ?>
                
                  <div class="form-group">
                    <label for="upload_types" class="col-lg-3 control-label">允许上传的图片类型</label>
                    <div class="col-lg-8">
                        <input type="text" name="upload_types" value="<?php echo @$model['upload_types']?>"  class="form-control" placeholder="">
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="upload_max_size" class="col-lg-3 control-label">准予上传的最大字节</label>
                    <div class="col-lg-8">
                        <input type="text" name="upload_max_size" value="<?php echo @$model['upload_max_size']?>" class="form-control" placeholder="">
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="mark_img_pos" class="col-lg-3 control-label">指定水印图片的位置</label>
                    <div class="col-lg-8">
                        <input type="text" name="mark_img_pos" value="<?php echo @$model['mark_img_pos']?>" class="form-control" placeholder="">
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="is_mark" class="col-lg-3 control-label">上传图片自动打水印</label>
                    <div class="col-lg-8">
                        <label class="radio-inline mr10">
                          <input type="radio" name="is_mark" <?php echo  @$model['is_mark'] == 0?'checked':'';?>  value="0">忽略打水印
                        </label>
                        <label class="radio-inline mr10">
                          <input type="radio" name="is_mark" <?php echo  @$model['is_mark'] == 1?'checked':'';?>  value="1">自动打水印
                        </label>
                    </div>
                  </div>

                  <div class="form-group">
                    <label for="real_del" class="col-lg-3 control-label">删除记录时删除文件</label>
                    <div class="col-lg-8">
                        <label class="radio-inline mr10">
                          <input type="radio" name="real_del" <?php echo  @$model['real_del'] == 0?'checked':'';?> value="0">忽略真实删除
                        </label>
                        <label class="radio-inline mr10">
                          <input type="radio" name="real_del" <?php echo  @$model['real_del'] == 1?'checked':'';?> value="1">执行真实删除
                        </label>
                    </div>
                  </div>
                 
                  <div class="form-group">
                    <label class="col-lg-3 control-label" for="textArea3"></label>
                    <div class="col-lg-8">
                        <input type="hidden"  name="_id" value="<?php echo @$_id?>" class="form-control" placeholder="">
                        <button type="submit" class="btn btn-default">submit</button>
                      
                    </div>
                  </div>
                <?php ActiveForm::end();?>
              </div>

</div>
      </div>
     
    </div>
  </div>
</div>
    
<?php //$this->registerJsFile('js/jquery.form.js',['position'=>3]) 
?>    
<script type="text/javascript">
<?php $this->beginBlock('js_system_config') ?> 
    jQuery(document).ready(function() {
        $('.panel-tabs').find('a').each(function(i){
            $(this).click(function(){
                if($(this).parent('li').hasClass('active')){

                }else{
                    var url = $(this).attr('href');
                    location.href=url;    
                }
                
            });
        });
        $('#quanju-form').ajaxSubmit({
            'beforeSubmit':function(){
                return false;
            },
            'success':function(){

            }
        });
  });
<?php $this->endBlock() ?>
</script>
  <?php $this->registerJs($this->blocks['js_system_config'], \yii\web\View::POS_END); ?>
		
	