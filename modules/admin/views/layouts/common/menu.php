        <?php 
        use yii\helpers\Url;
          //$cuAc = $this->context->module->requestedRoute;
            $route = Yii::$app->request->resolve();
            $cuAc = $route[0];
            $module_name = \Yii::$app->getModule('admin')->id;
        ?>
        <ul class="nav sidebar-menu">
          <li class="sidebar-label pt20">Menu</li>
          <li>
            <a href="index.php">
              <span class="glyphicon glyphicon-home"></span>
              <span class="sidebar-title">首页</span>
            </a>
          </li>
          <li class="sidebar-label pt15"></li>

          <?php 
            $mberArr = [ $module_name.'/mber/for72h', $module_name.'/mber/cao3ms', $module_name.'/mber/alllist', $module_name.'/mber/lianhao', $module_name.'/mber/dxuanhao', $module_name.'/mber/loginset' ];
          ?>          
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $mberArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-globe"></span>
              <span class="sidebar-title">会员管理</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo $cuAc=="admin/mber/for72h"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('mber/for72h')?>">
                  <span class="glyphicon glyphicon-shopping-cart"></span>72H's注册的</a>
              </li>
              <li <?php echo $cuAc=="admin/mber/cao3ms"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('mber/cao3ms')?>">
                  <span class="glyphicon glyphicon-calendar"></span>超3个月未登录的</a>
              </li>
              <li <?php echo $cuAc=="admin/mber/alllist"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('mber/alllist')?>">
                  <span class="glyphicon glyphicon-record"></span>全部列表</a>
              </li> 
              <li <?php echo $cuAc=="admin/mber/loginset"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('mber/loginset')?>">
                  <span class="fa fa-desktop"></span> 会员登录痕迹 </a>
              </li>

              <li <?php echo $cuAc=="admin/mber/dxuanhao"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('mber/dxuanhao')?>">
                  <span class="glyphicon glyphicon-list"></span>HHJJ普号库</a>
              </li>              
              <li <?php echo $cuAc=="admin/mber/lianhao"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('mber/lianhao')?>">
                  <span class="glyphicon glyphicon-paperclip"></span>HHJJ靓号库</a>
              </li>

            </ul>
          </li>

          <?php 
            $yxiaoArr = [ $module_name.'/yxiao/category', $module_name.'/yxiao/for24h', $module_name.'/yxiao/alllist', $module_name.'/yxiao/shenqin', $module_name.'/yxiao/yxiaoer', $module_name.'/yxiao/pinlun' ];
          ?>
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $yxiaoArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-link"></span>
              <span class="sidebar-title">音效(师)管理</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo $cuAc=="yxiao/category"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('yxiao/category')?>">
                  <span class="glyphicon glyphicon-shopping-cart"></span>音效分类</a>
              </li>
              <li <?php echo $cuAc=="yxiao/for24h"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('yxiao/for24h')?>">
                  <span class="glyphicon glyphicon-calendar"></span>24H's最新音效</a>
              </li>
              <li <?php echo $cuAc=="yxiao/alllist"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('yxiao/alllist')?>">
                  <span class="glyphicon glyphicon-gift"></span>音效列表</a>
              </li>              
              <li <?php echo $cuAc=="yxiao/shenqin"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('yxiao/shenqin')?>">
                  <span class="fa fa-desktop"></span> 音效师申请 </a>
              </li>
              <li <?php echo $cuAc=="yxiao/yxiaoer"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute('yxiao/yxiaoer')?>">
                  <span class="fa fa-clipboard"></span> 音效师 </a>
              </li>
              <li <?php echo $cuAc=="yxiao/pinlun"?"class='active'":'';?>> 
                <a href="<?php echo Url::toRoute('yxiao/pinlun')?>">
                  <span class="glyphicon glyphicon-shopping-cart"></span> 评论信息 </a>
              </li>
            </ul>
          </li>

          <?php 
            $wbarArr = [ $module_name.'/wbar/categoryindex', $module_name.'/wbar/for24h', $module_name.'/wbar/index' ];
          ?>
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $wbarArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-barcode"></span>
              <span class="sidebar-title">问吧管理</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo ($cuAc=="admin/wbar/categoryindex")?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("wbar/categoryindex");?>">
                  <span class="glyphicon glyphicon-book"></span>问题分类</a>
              </li>
              <li <?php echo ($cuAc=="admin/wbar/index" && $this->params['limit']==24)?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("wbar/for24h");?>">
                  <span class="glyphicon glyphicon-modal-window"></span>24H's最新问题</a>
              </li>
              <li <?php echo ($cuAc=="admin/wbar/index" && $this->params['limit']=='')?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("wbar/index");?>">
                  <span class="glyphicon glyphicon-modal-window"></span>全部列表</a>
              </li>             
            </ul>
          </li>

          <?php 
            $adsArr = [ $module_name.'/ads/index', $module_name.'/ads/alllocal', $module_name.'/ads/addads', $module_name.'/ads/allads' ];
          ?>          
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $adsArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-qrcode"></span>
              <span class="sidebar-title">广告位管理</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo $cuAc=="admin/ads/index"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("ads/index");?>">
                  <span class="glyphicon glyphicon-shopping-cart"></span>新增广告位</a>
              </li>
              <li <?php echo $cuAc=="admin/ads/alllocal"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("ads/alllocal");?>">
                  <span class="glyphicon glyphicon-calendar"></span>广告位列表</a>
              </li>
              <li <?php echo $cuAc=="admin/ads/addads"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("ads/addads");?>">
                  <span class="glyphicon glyphicon-calendar"></span>新增广告</a>
              </li>              
              <li <?php echo $cuAc=="admin/ads/allads"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("ads/allads");?>">
                  <span class="fa fa-desktop"></span>广告列表</a>
              </li>
            </ul>
          </li> 
          
          <?php 
            $sysArr = [ 'bigdata/yxiao', 'bigdata/wbar', 'bigdata/change' ];
          ?>
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $sysArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-retweet"></span>
              <span class="sidebar-title">用户行为记录仪</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo $cuAc=="bigdata/yxiao"?"class='active'":'';?>>
                <a href="index.php?r=bigdata/yxiao">
                  <span class="glyphicon glyphicon-compressed"></span>音效类</a>
              </li>

              <li <?php echo $cuAc=="bigdata/wbar"?"class='active'":'';?>>
                <a href="index.php?r=bigdata/wbar">
                  <span class="glyphicon glyphicon-cloud-download"></span>问吧类</a>
              </li>              
              <li <?php echo $cuAc=="bigdata/change"?"class='active'":'';?>>
                <a href="index.php?r=bigdata/change">
                  <span class="glyphicon glyphicon-screenshot"></span>充值交易类</a>
              </li>
            </ul>
          </li>

          <?php 
            $sysArr = [ 'client/tousu', 'client/jianyi', 'client/diancha' ];
          ?>
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $sysArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-floppy-saved"></span>
              <span class="sidebar-title">投诉与建议及调查</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo $cuAc=="client/tousu"?"class='active'":'';?>>
                <a href="index.php?r=client/tousu">
                  <span class="glyphicon glyphicon-tree-conifer"></span>用户投诉</a>
              </li>

              <li <?php echo $cuAc=="client/jianyi"?"class='active'":'';?>>
                <a href="index.php?r=client/jianyi">
                  <span class="glyphicon glyphicon-tags"></span>产品建议</a>
              </li>              
              <li <?php echo $cuAc=="client/diancha"?"class='active'":'';?>>
                <a href="index.php?r=client/diancha">
                  <span class="glyphicon glyphicon-send"></span>调查问卷题库</a>
              </li>
            </ul>
          </li>

          <?php 
            $sysArr = [ 'admin/system/quanju', 'admin/system/upsets', 'admin/system/keysku' ];
          ?>
          <li>
            <a class="accordion-toggle <?php echo in_array($cuAc, $sysArr)?"menu-open":""?>" href="#">
              <span class="glyphicon glyphicon-retweet"></span>
              <span class="sidebar-title">系统设置</span>
              <span class="caret"></span>
            </a>
            <ul class="nav sub-nav">
              <li <?php echo $cuAc=="admin/system/quanju"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("system/quanju");?>">
                  <span class="glyphicon glyphicon-shopping-cart"></span>全局变量</a>
              </li>

              <li <?php echo $cuAc=="admin/system/upsets"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("system/upsets");?>">
                  <span class="glyphicon glyphicon-shopping-cart"></span>上传设置</a>
              </li>              
              <li <?php echo $cuAc=="admin/system/keysku"?"class='active'":'';?>>
                <a href="<?php echo Url::toRoute("system/keysku");?>">
                  <span class="glyphicon glyphicon-calendar"></span>敏感关键词库</a>
              </li>
            </ul>
          </li> 

        </ul>