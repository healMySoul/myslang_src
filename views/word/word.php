<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

use app\components\Helpers;

$view	= !empty($view);
$src	= isset($src) ? $src : '';

if ($view)
{
	$this->title = $word->metaTitle;
}

$this->params['metaKeywords'] = $word->preparedMetaKeywords;
$this->params['metaDescription'] = $word->metaDescription;
?>
<article class="row word <?php if (!$view): ?>mb35<?php else: ?>view<?php endif; ?>" data-id="<?= $word->id ?>">
	<div class="col-xs-12<?php if ($view) { echo ' col-md-7'; } ?>">
		<?php if (!empty($word->tags)): ?>
			<ul class="tags hz">
				<?php foreach ($word->tags as $tag): ?>
					<li><a href="<?= Url::to(['word/list', 'filterType' => 'tag', 'filter' => $tag->sortName]) ?>"><?= Html::encode($tag->name) ?></a></li>
				<?php endforeach;?>
			</ul>
		<?php endif; ?>

		<a class="name" href="<?= Url::to(['word/view', 'nameTranslit' => $word->nameTranslit])  ?>"><?= Html::encode($word->name) ?></a>
		
		<?php
			$defs = $word->wordDefinitions;
			reset($defs);
			$fk = key($defs);
		?>

		<?php foreach ($defs as $k => $v): ?>
			<?= $this->render('definition', ['def' => $v, 'wordView' => $view, 'first' => ($k == $fk ? true : false)]) ?>
		<?php endforeach; ?>
	</div>

	<?php if (isset($asideList)): ?>
		<div class="go-next col-xs-12 col-md-4 col-md-offset-1">
			<?= $asideList ?>
		</div>
	<?php endif; ?>
</article>