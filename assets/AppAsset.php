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
class AppAsset extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $css = [
		'css/bootstrap.scss',
		'css/main.scss',
	];
	public $js = [
		'js/main.js',
		'js/jquery.cookie.js',
		'js/social-likes.min.js',
	];
	public $depends = [
		'yii\web\YiiAsset',
		'app\assets\FontAwesomeAsset',
		'yii\jui\JuiAsset',
	];
}
