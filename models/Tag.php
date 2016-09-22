<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "tag".
 *
 * @property integer $id
 * @property string $name
 * @property string $sortName 
 * @property integer $wordCount 
 *
 * @property WordTag[] $wordTags
 */
class Tag extends \yii\db\ActiveRecord
{
	public $newWordCount		= 0;
	public $newWordCountTest	= 0;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'TABLE_FOR_TAGS';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['wordCount'], 'integer'],
			[['name', 'sortName'], 'string', 'max' => 255],
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id' => 'ID',
			'name' => 'Name',
			'sortName' => 'Sort Name', 
			'wordCount' => 'Word Count',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWordTags()
	{
		return $this->hasMany(WordTag::className(), ['tagId' => 'id']);
	}

	/**
     * @return \yii\db\ActiveQuery
     */
    public function getWordViews()
    {
    	return $this->hasMany(WordView::className(), ['wordId' => 'wordId'])
        	->viaTable('word_tag', ['tagId' => 'id']);
    }

	/**
	 * Функция подсчета количества слов, для которых использовался каждый тег. Нужно для подсчета популярности тега.
	 */
	public static function countWords()
	{
		$tags = static::find()
			->select(['TABLE_FOR_TAGS.id', 'COUNT(TABLE_FOR_WORD_TAGS.wordId) as wordCount, COUNT(TABLE_FOR_WORD_DEFINITIONS.id) as wordCountTest'])
			->leftJoin('TABLE_FOR_WORD_TAGS', 'TABLE_FOR_WORD_TAGS.tagId = TABLE_FOR_TAGS.id')
			->leftJoin('TABLE_FOR_WORD_DEFINITIONS', 'TABLE_FOR_WORD_DEFINITIONS.wordId = TABLE_FOR_WORD_TAGS.wordId AND TABLE_FOR_WORD_DEFINITIONS.question IS NOT NULL AND TABLE_FOR_WORD_DEFINITIONS.question != ""')
			->groupBy('TABLE_FOR_WORD_TAGS.id')
			->all();

		foreach ($tags as $tag)
		{
			$tag->setOldAttribute('wordCount', 0);
			$tag->setOldAttribute('wordCountTest', 0);
			$tag->update();
		}
	}

	public static function mainList($src)
	{
		$userId = Yii::$app->user->id;

		$select = ['t.*', '(t.wordCount - COUNT(wv.wordId = wt.wordId)) as newWordCount'];

		$query = static::find()
			->from('TABLE_FOR_WORD_TAGS as t')
			->leftJoin('TABLE_FOR_WORD_TAGS AS wt', 'wt.tagId = t.id')
			->leftJoin('TABLE_FOR_WORD_VIEWS AS wv', 'wv.wordId = wt.wordId AND wv.userId = "' . $userId . '"')
			->groupBy(['t.id'])
			;

		switch ($src)
		{
			case 'testStart':
				$select[]	= '(t.wordCountTest - COUNT(wv.wordId = wt.wordId)) as newWordCountTest';
				$limit		= null;
				$order		= ['t.wordCountTest' => SORT_DESC, 't.sortName' => SORT_ASC];
				$where[]	= ['>', 't.wordCountTest', 5];
				break;
			
			default:
				$limit = null;
				$order = ['t.wordCount' => SORT_DESC, 't.sortName' => SORT_ASC];
				$where = [];
				break;
		}

		$query->select($select);
		$query->limit($limit);
		$query->orderBy($order);

		foreach ($where as $k => $v)
		{
			$query->where($v);
		}

		$tags = $query->all();

		return $tags;
	}

	public function saveTag($name, $main = 0)
	{
		$name = trim($name);
		$name = mb_strtolower($name, 'UTF-8');
		$name = preg_replace('/[^0-9a-zа-яё\s]/u', '', $name);

		if (!empty($name))
		{
			$tag = static::findOne(['name' => $name]);

			if (!$tag)
			{
				$this->name		= $name;
				$this->sortName	= preg_replace('/\s+/u', '-', $name);
				$this->main		= $main;
				$this->save();

				return $this;
			}

			return $tag;	
		}

		return false;
	}
}
