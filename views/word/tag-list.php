<?php
use yii\helpers\Html;
use yii\helpers\Url;
?>
<ul class="tag-list unstyled clearfix">
	<?php foreach ($tags as $tag): ?>
		<?php
			switch ($src)
			{
				case 'words':
					$wordCount		= $tag->wordCount;
					$newWordCount	= $tag->newWordCount;
					$urlTo			= ['word/list', 'filterType' => 'tag', 'filter' => $tag->sortName];
					break;

				case 'test':
					$wordCount		= $tag->wordCountTest;
					$newWordCount	= $tag->newWordCountTest;
					$urlTo			= ['test/start', 'tagSortName' => $tag->sortName];
					break;
				
				default:
					$wordCount		= $tag->wordCount;
					$newWordCount	= $tag->newWordCount;
					$urlTo			= '';
					break;
			}
		?>

		<li>
			<a href="<?= Url::to($urlTo) ?>"><?= Html::encode($tag->name) ?></a>
			<span class="wordCount"><?= $wordCount ?></span>
			<span class="newWordCount"><?php if ($newWordCount > 0) { echo Helpers::wordEnd($newWordCount, 'новое'); } ?></span>
		</li>
	<?php endforeach;?>
</ul>