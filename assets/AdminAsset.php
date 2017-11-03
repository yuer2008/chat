<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

/**
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class AdminAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        // 'import/css/bootstrap.min.css',
        'import/css/ace.min.css',
        'import/css/ace-rtl.min.css',
        'import/css/ace-skins.min.css',
        'import/css/font-awesome.min.css',
    ];
    public $js = [
        'import/js/ace-extra.min.js',
        'import/js/ace-extra.min.js',
        'import/js/bootstrap.min.js',
        'import/js/typeahead-bs2.min.js',
        'import/js/ace-elements.min.js',
        'import/js/ace.min.js'
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
