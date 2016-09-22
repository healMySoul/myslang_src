<?php
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = $title != null ? $title : 'Словарь современного языка. Современные, новые, модные слова';
?>
<section id="words">
	<div class="heading"><?php if ($filterType != null) { echo $filterLit; } else { echo 'Современные слова по темам'; } ?></div>

	<div class="row">
		<div class="wordsWrap col-xs-12 col-md-7" data-page="1" data-url="<?= Url::current() ?>">
			<form id="search-form" class="mb15" action="<?= Url::to(['word/list', 'filterType' => 'search', 'filter' => 'query']) ?>" method="get">
				<input type="text" name="q" value="<?= $q ?>">
				<div class="go"></div>
			</form>

			<?php if ($filterType != null && empty($words)): ?>
				<div class="mt10">Подходящих слов не найдено.</div>
			<?php endif; ?>

			<?php if (!empty($tags)): ?>
				<?= $this->render('tag-list', ['tags' => $tags, 'src' => 'words']) ?>
			<?php endif; ?>
			
			<?php foreach ($words as $word): ?>
				<?= $this->render('word', ['word' => $word]) ?>
			<?php endforeach;?>
		</div>
		
		<div class="col-xs-12 col-sm-12 col-md-4 col-md-offset-1">
			<?= $asideList ?>
			
			<?= YII_ENV == 'dev' ? '' : $this->render('/site/social-buttons', ['addClasses' => !empty($words) ? 'hidden-sm hidden-xs' : '']) ?>
		</div>
	</div>
</section>