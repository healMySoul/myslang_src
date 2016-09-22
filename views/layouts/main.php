<?php

use app\assets\AppAsset;
use app\assets\NoFlexboxAsset;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Breadcrumbs;
use yii\widgets\Spaceless;

AppAsset::register($this);
NoFlexboxAsset::register($this);

$this->registerMetaTag(['name' => 'keywords', 'content' => (!empty($this->params['metaKeywords']) ? $this->params['metaKeywords'] : 'словарь, современный, слово, русский, язык, новый, модный, молодежный, сленг, термины, интернет, что, значит, значение, слова, тема ')], 'metaKeywords');
$this->registerMetaTag(['name' => 'description', 'content' => (!empty($this->params['metaDescription']) ? $this->params['metaDescription'] : 'Словарь современного русского языка. Новый модные слова, молодежный сленг, терминология, интернет-сленг.')]);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
	<meta charset="<?= Yii::$app->charset ?>"/>
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="shortcut icon" href="/favicon.ico?v=2">
	<?= Html::csrfMetaTags() ?>
	<title><?= Html::encode($this->title) ?></title>
	<?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>
<?php Spaceless::begin(); ?>
	<header id="main-header">
		<div class="container">
			<div class="mobile-wrap">
				<div id="mobile-menu-btn" class="fa fa-bars"></div>

				<div class="main-logo">
					<a href="<?= Url::to(['word/list']) ?>"><img src="/img/logo.png?v=2"></a>
					<span>Словарь современного языка</span>
				</div>

				<div id="mobile-menu">
					<ul>
						<li><a href="<?= Url::to(['words/']) ?>">Все</a></li>
						<li><a href="<?= Url::to(['words/new']) ?>">Новое</a></li>
						<li><a href="<?= Url::to(['words/popular']) ?>">Популярное</a></li>

						<li><a href="<?php if (Yii::$app->user->isGuest) { echo Url::to(['site/login', 'for' => 'add-word']); } else { echo Url::to(['word/edit']); } ?>">Добавить слово</a></li>

						<?php if (Yii::$app->user->isGuest): ?>
							<li><a href="<?= Url::to(['site/login']) ?>">Войти</a></li>
						<?php else: ?>
							<li>
								<a href="<?= Url::to(['site/logout']) ?>">Выйти</a>

								<a class="userpic" href="<?= Url::to(['site/profile', 'id' => Yii::$app->user->identity->id]) ?>">
									<img src="<?= Yii::$app->user->identity->picPath != null ? Yii::$app->user->identity->picPath : '/img/no-userpic-40.png' ?>">
								</a>
							</li>
						<?php endif; ?>
						
						<li>
							<form action="<?= Url::to(['word/list', 'filterType' => 'search', 'filter' => 'query']) ?>" method="get">
								<input type="text" name="q" placeholder="ПОИСК">
							</form>
						</li>
					</ul>
				</div>
			</div>
			
			<div class="desktop-wrap">
				<div class="main-logo">
					<a href="<?= Url::to(['word/list']) ?>"><img src="/img/logo.png?v=2"></a>
					<span>Словарь современного языка</span>
				</div>

				<div id="desktop-menu">
					<ul>
						<li><a href="<?= Url::to(['words/']) ?>">Все</a></li>
						<li><a href="<?= Url::to(['words/new']) ?>">Новое</a></li>
						<li><a href="<?= Url::to(['words/popular']) ?>">Популярное</a></li>

						<li><a href="<?php if (Yii::$app->user->isGuest) { echo Url::to(['site/login', 'for' => 'add-word']); } else { echo Url::to(['word/edit']); } ?>">Добавить слово</a></li>

						<?php if (Yii::$app->user->isGuest): ?>
							<li><a href="<?= Url::to(['site/login']) ?>">Войти</a></li>
						<?php else: ?>
							<li>								
								<a class="userpic" href="<?= Url::to(['site/profile', 'id' => Yii::$app->user->identity->id]) ?>">
									<img src="<?= Yii::$app->user->identity->picPath != null ? Yii::$app->user->identity->picPath : '/img/no-userpic-40.png' ?>">
								</a>

								<a href="<?= Url::to(['site/logout']) ?>">Выйти</a>
							</li>
						<?php endif; ?>
					</ul>
				</div>
			</div>
		</div>
	</header>

	<main>
		<div class="container">
			<?php if (Yii::$app->session->hasFlash('success')): ?>
				<div class="flash-msg success"><?= Yii::$app->session->getFlash('success'); ?></div>
			<?php endif; ?>

			<?php if (Yii::$app->session->hasFlash('error')): ?>
				<div class="flash-msg error"><?= Yii::$app->session->getFlash('error'); ?></div>
			<?php endif; ?>

			<?= Breadcrumbs::widget([
				'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
			]) ?>

			<?= $content ?>
		</div>
	</main>

	<footer id="main-footer">
        <?php if (YII_ENV != 'dev'): ?>
            <!-- Yandex.Metrika counter -->
            <!-- /Yandex.Metrika counter -->

            <!-- Google analytics -->
        <?php endif; ?>
	</footer>
<?php Spaceless::end(); ?>
<?php $this->endBody(); ?>
</body>
</html>
<?php $this->endPage(); ?>
