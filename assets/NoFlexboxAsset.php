<?php

namespace app\assets;

use yii\web\AssetBundle;

class NoFlexboxAsset extends AssetBundle
{
	public $basePath = '@webroot';
	public $baseUrl = '@web';
	public $cssOptions = [
		'condition' => 'lte IE 10'
	];
	public $css = [
		'css/no-flexbox.scss',
	];
	public $js = [
	];
}
