  <div id="skin-toolbox">
    <div class="panel">
      <div class="panel-heading">
        <span class="panel-icon">
          <i class="fa fa-gear text-primary"></i>
        </span>
        <span class="panel-title">主题选项</span>
      </div>
      <div class="panel-body pn">
        <ul class="nav nav-list nav-list-sm pl15 pt10" role="tablist">
          <li class="active">
            <a href="#toolbox-header" role="tab" data-toggle="tab">导航栏</a>
          </li>
          <li>
            <a href="#toolbox-sidebar" role="tab" data-toggle="tab">侧边栏</a>
          </li>
          <li>
            <a href="#toolbox-settings" role="tab" data-toggle="tab">其他</a>
          </li>
        </ul>
        <div class="tab-content p20 ptn pb15">
          <div role="tabpanel" class="tab-pane active" id="toolbox-header">
            <?php include_once("header_skins.php");?>
          </div>
          <div role="tabpanel" class="tab-pane" id="toolbox-sidebar">
            <?php include_once("sidebar_skins.php");?>
          </div>
          <div role="tabpanel" class="tab-pane" id="toolbox-settings">
            <?php include_once("layouts.php");?>
          </div>
        </div>
        <div class="form-group mn br-t p15">
          <a href="#" id="clearLocalStorage" class="btn btn-primary btn-block pb10 pt10">清除缓存</a>
        </div>
      </div>
    </div>
  </div>