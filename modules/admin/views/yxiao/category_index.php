<?php
use yii\widgets\LinkPager;
use yii\widgets\ActiveForm;
?>
<!DOCTYPE html>
<html>

<head>
  <!-- Meta, title, CSS, favicons, etc. -->
  <meta charset="utf-8">
  <title>音效师列表</title>
  <meta name="keywords" content="Bootstrap 3 Admin Dashboard Template Theme" />
  <meta name="description" content="AdminDesigns - Bootstrap 3 Admin Dashboard Theme">
  <meta name="author" content="AdminDesigns">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">

  <!-- Datatables CSS -->
  <link rel="stylesheet" type="text/css" href="vendor/plugins/datatables/media/css/dataTables.bootstrap.css">

  <!-- Datatables Editor Addon CSS -->
  <link rel="stylesheet" type="text/css" href="vendor/plugins/datatables/extensions/Editor/css/dataTables.editor.css">

  <!-- Datatables ColReorder Addon CSS -->
  <link rel="stylesheet" type="text/css" href="vendor/plugins/datatables/extensions/ColReorder/css/dataTables.colReorder.min.css">

  <!-- Theme CSS -->
  <link rel="stylesheet" type="text/css" href="assets/skin/default_skin/css/theme.css">
  <link rel="stylesheet" type="text/css" href="css/bootstrap-editable.css">


  <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
  <!--[if lt IE 9]>
  <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
  <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
<![endif]-->

</head>

<body class="datatables-page" data-spy="scroll" data-target="#nav-spy" data-offset="300">

  <!-- Start: Theme Preview Pane -->
    <?php include_once("common/themepane.php");?>
  <!-- End: Theme Preview Pane -->

  <!-- Start: Main -->
  <div id="main">

    <!-- Start: Header -->
      <?php include_once("common/header.php");?>
    <!-- End: Header -->

    <!-- Start: Sidebar -->
    <aside id="sidebar_left" class="nano nano-light affix">

      <!-- Start: Sidebar Left Content -->
      <div class="sidebar-left-content nano-content">

        <!-- Start: Sidebar Header -->
        <header class="sidebar-header">

          <!-- Sidebar Widget - Author -->
          <?php include_once("common/avatvar.php");?>

          <!-- Sidebar Widget - Menu (slidedown) -->
          <div class="sidebar-widget menu-widget">
            <div class="row text-center mbn">
              <div class="col-xs-4">
                <a href="dashboard.html" class="text-primary" data-toggle="tooltip" data-placement="top" title="Dashboard">
                  <span class="glyphicon glyphicon-home"></span>
                </a>
              </div>
              <div class="col-xs-4">
                <a href="pages_messages.html" class="text-info" data-toggle="tooltip" data-placement="top" title="Messages">
                  <span class="glyphicon glyphicon-inbox"></span>
                </a>
              </div>
              <div class="col-xs-4">
                <a href="pages_profile.html" class="text-alert" data-toggle="tooltip" data-placement="top" title="Tasks">
                  <span class="glyphicon glyphicon-bell"></span>
                </a>
              </div>
              <div class="col-xs-4">
                <a href="pages_timeline.html" class="text-system" data-toggle="tooltip" data-placement="top" title="Activity">
                  <span class="fa fa-desktop"></span>
                </a>
              </div>
              <div class="col-xs-4">
                <a href="pages_profile.html" class="text-danger" data-toggle="tooltip" data-placement="top" title="Settings">
                  <span class="fa fa-gears"></span>
                </a>
              </div>
              <div class="col-xs-4">
                <a href="pages_gallery.html" class="text-warning" data-toggle="tooltip" data-placement="top" title="Cron Jobs">
                  <span class="fa fa-flask"></span>
                </a>
              </div>
            </div>
          </div>

          <!-- Sidebar Widget - Search (hidden) -->
          <div class="sidebar-widget search-widget hidden">
            <div class="input-group">
              <span class="input-group-addon">
                <i class="fa fa-search"></i>
              </span>
              <input type="text" id="sidebar-search" class="form-control" placeholder="Search...">
            </div>
          </div>

        </header>
        <!-- End: Sidebar Header -->

        <!-- Start: Sidebar Menu -->
        <?php include_once("common/menu.php");?>  
        <!-- End: Sidebar Left Menu -->

        <!-- Start: Sidebar Collapse Button -->
        <div class="sidebar-toggle-mini">
          <a href="#">
            <span class="fa fa-sign-out"></span>
          </a>
        </div>
        <!-- End: Sidebar Collapse Button -->

      </div>
      <!-- End: Sidebar Left Content -->

    </aside>

    <!-- Start: Content-Wrapper -->
    <section id="content_wrapper">

      <!-- Start: Topbar -->
      <header id="topbar" class="alt">
        <div class="topbar-left">
          <ol class="breadcrumb">
            <li class="crumb-active">
              <a href="dashboard.html">信息概况</a>
            </li>
            <li class="crumb-icon">
              <a href="dashboard.html">
                <span class="glyphicon glyphicon-home"></span>
              </a>
            </li>
            <li class="crumb-link">
              <a href="index.html">首页</a>
            </li>
            <li class="crumb-trail">音效师列表</li>
          </ol>
        </div>
      </header>
      <!-- End: Topbar -->

      <!-- Begin: Content -->
      <section id="content" class="table-layout animated fadeIn">

        <!-- begin: .tray-left -->
        <!-- end: .tray-left -->

        <!-- begin: .tray-center -->
        <div class="tray tray-center">

           <div class="row">
            <?php
                            $form = yii\widgets\ActiveForm::begin([
                                'id' => 'add-form',
                                'action' =>'index.php?r=yxiao/categoryadd',
                                'method' =>'post',
                                'enableAjaxValidation' => false,
                                // 'enableAjaxValidation'   => true,
                                // 'enableClientValidation' => false,
                                'options' => ['class' => 'form-inline'],
                            ]);
                        ?>
                        
                        <div class="form-group">
                            <label for="exampleInputName2"></label>
                            <input type="search" name="name" class="form-control input-sm" id="name" value="" placeholder="分类名...">
                            
                        </div>
                        <button type="submit" class="btn btn-default">新增</button>
                        
                        <?php ActiveForm::end();?>


            <div class="col-md-12">
              <div class="panel panel-visible" id="spy3">
                <div class="panel-heading">
                    <div class="panel-title hidden-xs">
                        <span class="glyphicon glyphicon-tasks"></span>...
                        
                    </div>
                </div>
                <div class="panel-body pn">
                  <table class="table table-striped table-hover" id="datatable3" cellspacing="0" width="100%">
                    <thead>
                      <tr >
                        <th width="40">No.</th>
                        <th width="80">音效分类</th>
                        <th width="200">排序</th>
                        <th width="150">操作</th>
                      </tr>
                    </thead>
                    
                    <tbody>
                      <?php foreach ($model as $ask){?>
                      <tr>
                        
                        <td><?php echo $ask->eid;?></td>
                        <td>
                            <a href="#" class="etype" data-post="edit" data-name="etype" data-type="text" data-pk=<?php echo $ask->_id;?> data-title="edit type">
                                <?php echo $ask->etype?>
                            </a>
                            
                        </td>
                        <td>
                            <a href="#" class="eorder" data-post="edit" data-name="eorder" data-type="text" data-pk=<?php echo $ask->_id;?> data-title="edit order">
                                <?php echo $ask->eorder?>
                            </a>
                        </td>
                        <td>
                            
                            
                            <span class="glyphicon glyphicon-remove del_btn" data-id="<?php echo $ask->_id?>"></span>
                            
                        </td>
                      </tr>
                      <?php }?>

                    </tbody>
                  </table>
                </div>
              </div>
            </div>

          </div>

        </div>
        <!-- end: .tray-center -->

      </section>
      <!-- End: Content -->

    </section>


  </div>
  <!-- End: Main -->


  <!-- BEGIN: PAGE SCRIPTS -->

  <!-- jQuery -->
  <script src="vendor/jquery/jquery-1.11.1.min.js"></script>
  <script src="vendor/jquery/jquery_ui/jquery-ui.min.js"></script>

  <!-- Datatables -->
  <script src="vendor/plugins/datatables/media/js/jquery.dataTables.js"></script>

  <!-- Datatables Tabletools addon -->
  <script src="vendor/plugins/datatables/extensions/TableTools/js/dataTables.tableTools.min.js"></script>

  <!-- Datatables ColReorder addon -->
  <script src="vendor/plugins/datatables/extensions/ColReorder/js/dataTables.colReorder.min.js"></script>

  <!-- Datatables Bootstrap Modifications  -->
  <script src="vendor/plugins/datatables/media/js/dataTables.bootstrap.js"></script>

  <!-- Theme Javascript -->
  <script src="assets/js/utility/utility.js"></script>
  <script src="assets/js/demo/demo.js"></script>
  <script src="assets/js/main.js"></script>
  <script src="js/bootstrap-editable.min.js"></script>
  <script type="text/javascript">
  jQuery(document).ready(function() {

    "use strict";

    // Init Theme Core    
    Core.init();

    // Init Demo JS  
    Demo.init();

    $.fn.editable.defaults.mode = 'inline';
    $('.etype').editable({
               url: 'index.php?r=yxiao/categoryedit',
               // type: 'text',
               //pk: 1,
               // name: 'username',
               // title: 'Enter username',
               success: function(response, newValue) {
                    
                    if(response.status == 'error') return response.msg; //msg will be shown in editable form

                }
    });
    $('.eorder').editable({
               url: 'index.php?r=yxiao/categoryedit',
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
                    url : 'index.php?r=yxiao/categorydel',
                    type : 'post',
                    data : {
                        pk : id
                     },
                    dataType : 'json',
                    success:function(_d){
                        // alert(_d.m);
                        if(_d.s == 1){
                            location.reload();
                        }
                    }
                });
            }

        });

  });
  </script>
  <!-- END: PAGE SCRIPTS -->

</body>

</html>
