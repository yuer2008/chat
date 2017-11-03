<?php
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;

?>
<div class="container">
    <?php
        $form = yii\widgets\ActiveForm::begin([
            'id' => 'search-form',
            'action' =>'index.php?r=wbar/index',
            'method' =>'post',
            'enableAjaxValidation' => false,
            // 'enableAjaxValidation'   => true,
            // 'enableClientValidation' => false,
            'options' => ['class' => 'form-inline'],
        ]);
    ?>
    
    <div class="form-group">
        <label for="exampleInputName2">Search</label>
        <input type="search" name="keyword" class="form-control input-sm" id="keyword" value="<?php echo $keyword?>" placeholder="keyword...">
        <input type="hidden" name="limit" id="limit" value="<?php echo $limit;?>">
    </div>
    <button type="submit" class="btn btn-default">search</button>
    <?php ActiveForm::end();?>
   

    <div class="row">
  <div class="panel panel-visible" id="spy2">
    <div class="panel-heading">
      <div class="panel-title hidden-xs">
        <span class="glyphicon glyphicon-tasks"></span>问题列表
      </div>
     
    </div>




    <div class="panel-body pn">
      <table class="table table-striped table-hover" id="datatable2" cellspacing="0" width="100%">
        <thead>
          <tr>
            <th>标题</th>
            <th>回答数</th>
            <th>发表时间</th>
            <th>操作</th>
           
          </tr>
        </thead>
        <tbody>
<?php foreach ($model as $ask){?>
          <tr>
            <td>
                <?php if($ask->reward){?>
                        <img src="<?php echo Yii::$app->request->baseUrl?>/images/money.png" width="15" height="12">
                        <span class="ask_list_money"><?php echo $ask->reward;?></span>
                <?php }?>
                <a href="<?php echo Yii::$app->urlManager->createUrl(['wbar/forone','ikey'=>(string)$ask->_id]) ?>"><?php echo $ask->title;?></a></td>
            <td><?php echo $ask->answer_num;?>回答</td>
            <td><?php echo date('Y-m-d H:i:s',$ask->add_time)?></td>
            <td>
                <!--
                    <span class="glyphicon glyphicon-pencil edit_btn" data-id="<?php echo $ask->ask_id?>"></span>
                -->
                
                <span class="glyphicon glyphicon-remove del_btn" data-id="<?php echo $ask->ask_id?>"></span>
                
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
        //删除
        $('.del_btn').on('click',function(){
            var id = $(this).data('id');
            if(confirm("确认删除?")){
                $.ajax({
                    url : 'index.php?r=wbar/delete',
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
		
	