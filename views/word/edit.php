<?php
use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\helpers\Url;

$this->title = 'Добавить слово';
?>
<div id="word-edit">
	<div class="row">		
		<?php if ($model->id > 0): ?>
			<div class="col-xs-12 col-sm-6 col-sm-pull-6 mb20">
				<div class="row-ttl">Автор</div>
				<?= $model->authorProfileLink ?></a>
			</div>
		<?php endif; ?>

		<?php $form = ActiveForm::begin([
			'id' => 'word-add-form',
			'options' => ['class' => 'form-horizontal col-xs-12 col-sm-6 col-sm-pull-6', 'autocomplete' => 'off'],
			'enableClientValidation' => true,
			'enableAjaxValidation' => true,
		]); ?>

		<?= $form->field($model, 'name', [
			'template' => '{label}<div class="col-xs-12">{input}{error}</div>',
			'labelOptions' => ['class' => 'col-xs-12'],
		])->textInput(['placeholder' => 'Напишите слово', 'class' => 'length-counting', 'maxlength' => 50]) ?>
		
		<?php
			for ($i = 0; $i <= 2; $i++)
			{
				$label = $i == 0 ? 'Теги' : false;
				$value = isset($model->tags[$i]) ? $model->tags[$i]->name : '';

				echo $form->field($model, 'postTags[]', [
						'template' => '{label}<div class="col-xs-12">{input}</div>',
						'labelOptions' => ['class' => 'col-xs-12'],
					])
					->label($label)
					->textInput([
						'id'				=> 'word-posttag-' . $i,
						'class'				=> 'length-counting',
						'maxlength'			=> 30,
						'placeholder'		=> 'Напишите тег',
						'value'				=> $value,
						'data-autocomplete' => 'wordEditTags',
					]);
			}
		?>

		<div class="form-group">
			<div class="col-xs-12">
				<?= Html::submitButton('Опубликовать', ['class' => 'form-btn']) ?>
			</div>
		</div>
		
		<ul>
			<?php foreach ($model->errors as $v): ?>
				<li><?= $v[0] ?></li>
			<?php endforeach; ?>
		</ul>

		<?php ActiveForm::end(); ?>
	</div>
</div>