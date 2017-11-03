<?php
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
?>
<div class="container">
    <?php
        $form = yii\widgets\ActiveForm::begin([
            'id' => 'search-form',
            'action' =>Url::toRoute('system/keysku'),
            'method' =>'post',
            'enableAjaxValidation' => false,
            // 'enableAjaxValidation'   => true,
            // 'enableClientValidation' => false,
            'options' => ['class' => 'form-inline'],
        ]);
    ?>
    
    <div class="form-group">
        <label for="exampleInputName2">Search</label>
        <input type="search" name="keyword" class="form-control input-sm" id="keyword" value="<?php echo @$keyword?>" placeholder="keyword...">
        
    </div>
    <button type="submit" class="btn btn-default">search</button>
    <a type="button" href="<?php Url::toRoute('system/keyadd');?>" class="btn active btn-success btn-inline" id="add">新增</a>
    <?php ActiveForm::end();?>
   

    <div class="row">
  <div class="panel panel-visible" id="spy2">
    <div class="panel-heading">
      <div class="panel-title hidden-xs">
        <span class="glyphicon glyphicon-tasks"></span>列表
      </div>
     
    </div>




    <div class="panel-body pn">
      <table class="table table-striped table-hover" id="datatable2" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th>敏感词</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
<?php foreach ($model as $keys){?>
          <tr>
                
            <td>
                <a href="#" class="username" data-post="edit" data-type="text" data-pk=<?php echo $keys->_id;?> data-title="edit keyword"><?php echo $keys->word_name;?></a>
                
            </td>
            
            <td>
                <span class="glyphicon glyphicon-remove del_btn" data-id="<?php echo $keys->_id?>"></span>
            </td>
          </tr>
          <?php }?>
          <tr>
            <td colspan="4">
                <?= LinkPager::widget(['pagination' => $pages,
            // 'activePageCssClass' => 'link_active',
            // 'disabledPageCssClass'=> 'page',
            'firstPageCssClass'=>'',
            'firstPageLabel'=>'第1页',
            'lastPageCssClass'=>'',
            'lastPageLabel'=>'第'.$pages->pageCount.'页',
            'nextPageLabel'=>false,
            'prevPageLabel'=>false,
            // 'linkOptions'=>['class'=>'link_page'],
            // 'options'=>['class'=>'pagemaCss'],
            'maxButtonCount'=>7,
            ]); 
            ?>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</div>
</div>


<script type="text/javascript">
<?php $this->beginBlock('js_search_table_for_wbar_list') ?> 
    jQuery(document).ready(function() {

        $.fn.editable.defaults.mode = 'inline'; // or popup
        //editables 
        $('.username').editable({
               url: "<?php echo Url::toRoute('system/edit');?>",
               // type: 'text',
               //pk: 1,
               // name: 'username',
               // title: 'Enter username',
               success: function(response, newValue) {
                    
                    if(response.status == 'error') return response.msg; //msg will be shown in editable form

                }
        });
       
        //删除
        $('.del_btn').on('click',function(){
            var id = $(this).data('id');
            if(confirm("确认删除?")){
                $.ajax({
                    url : "<?php echo Url::toRoute('system/keydel');?>",
                    type : 'get',
                    data : {
                        id : id
                     },
                    dataType : 'json',
                    success:function(_d){
                        alert(_d.m);
                        if(_d.s == 1){
                            location.reload();
                        }
                    }
                });
            }

        });
  });
<?php $this->endBlock() ?>
</script>
  <?php $this->registerJs($this->blocks['js_search_table_for_wbar_list'], \yii\web\View::POS_END); ?>
		
	