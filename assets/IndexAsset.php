<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2015/6/16
 * Time: 13:53
 */
namespace app\assets;

use yii\web\AssetBundle;

class IndexAsset extends  AssetBundle{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $css = [
        'Import/skin/default_skin/css/theme.css',
        'Import/admin-tools/admin-forms/css/admin-forms.css',
    ];
    public $js = [
        'js/jquery/jquery_ui/jquery-ui.min.js',
        'js/plugins/canvasbg/canvasbg.js',
        'Import/js/utility/utility.js',
        'Import/js/demo/demo.js',
        'Import/js/main.js',
        'js/index.js',
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];

}
