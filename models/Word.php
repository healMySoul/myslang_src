<?php
namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

use app\components\Helpers;
use app\components\MathCombinatorics;
use app\components\WordBehavior;
use app\models\Tag;

/**
 * This is the model class for table "word".
 *
 * @property integer $id
 * @property string $name
 * @property string $nameTranslit
 * @property integer $authorId
 *
 * @property User $author
 * @property WordDefinition[] $wordDefinitions
 * @property WordSibling[] $wordSiblings 
 * @property WordTag[] $wordTags
 */
class Word extends ActiveRecord
{
	public $authorProfileLink	= '';
	public $postDefinitions		= [];
	public $postTags			= [];
	public $randomSiblings		= [];

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'TABLE_FOR_WORDS';
	}

	/**
	 * @inheritdoc
	 */
	public function rules()
	{
		return [
			[['name'], 'required', 'message' => 'Название не может быть пустым.'],
			[['name'], 'trim'],
			[['name'], 'unique', 'targetClass' => self::className(), 'message' => ''],
			[['name'], 'string', 'max' => 50],
			[['nameTranslit'], 'unique', 'targetClass' => self::className(), 'message' => 'Такой транслит уже есть'],
			[['nameTranslit'], 'string', 'max' => 200],
			[['authorId'], 'integer'],
			[['name', 'metaTitle', 'metaDescription', 'metaKeywords'], 'string', 'max' => 1000],
		];
	}

	public function behaviors()
	{
		return [
			[
				'class' => TimestampBehavior::className(),
				'attributes' => [
					ActiveRecord::EVENT_BEFORE_INSERT => ['dateCreated'],
					ActiveRecord::EVENT_BEFORE_UPDATE => ['dateEdited'],
				],
				'value' => new Expression('NOW()'),
			],
			[
				'class' => WordBehavior::className(),
			],
		];
	}

	// сценарии для ограничения полей для сохранения, когда слово сохраняет юзер.
	public function scenarios()
	{
		$scenarios = parent::scenarios();
		$scenarios['createByUser'] = ['name', 'authorId'];

		return $scenarios;
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		return [
			'id'				=> 'ID',
			'name'				=> 'Слово',
			'nameTranslit'		=> 'Слово транслитом',
			'authorId'			=> 'Автор',
			'metaTitle'			=> 'Meta-Title',
			'metaDescription'	=> 'Meta-Description',
			'metaKeywords'		=> 'Meta-Keywords',
		];
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getAuthor()
	{
		return $this->hasOne(User::className(), ['id' => 'authorId']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWordDefinitions()
	{
		return $this->hasMany(WordDefinition::className(), ['wordId' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWordTags()
	{
		return $this->hasMany(WordTag::className(), ['wordId' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getTags()
	{
		return $this->hasMany(Tag::className(), ['id' => 'tagId'])
			->via('wordTags')
			->orderBy(['TABLE_FOR_TAGS.name' => SORT_ASC]);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getWordSiblings()
	{
		return $this->hasMany(WordSibling::className(), ['wordId' => 'id']);
	}

	/**
	 * @return \yii\db\ActiveQuery
	 */
	public function getSiblings()
	{
		return $this->hasMany(Word::className(), ['id' => 'siblingId'])
			->via('wordSiblings');
	}

	public function saveWord($admin = false)
	{
		if ($this->validate())
		{
			if ($this->id == null)
			{
				$this->authorId = Yii::$app->user->isGuest ? null : Yii::$app->user->identity->id;
			}

			// сохранение падежей происходит в дефолтном сценарии
			switch ($this->scenario)
			{
				case 'default':
					break;
				
				case 'createByUser':
					$this->metaTitle = 'Что такое ' . $this->name . '?';
					break;
			}
			
			$this->save();

			// удаление старых тэгов
			$this->unlinkAll('tags', true);

			// тэгов должно быть не больше 3-х
			$postTags		= array_slice($this->postTags, 0, 3);
			$savedTagIds	= [];

			foreach ($postTags as $postTag)
			{
				$tag = new Tag();
				$tag = $tag->saveTag($postTag);

				if ($tag !== false && !in_array($tag->id, $savedTagIds))
				{
					$savedTagIds[] = $tag->id;
					$this->link('tags', $tag);
				}
			}

			return $this;
		}

		return null;
	}

	public function getWord($id, $src = null)
	{
		$query = static::find()->joinWith(['tags']);

		if ($src == 'view')
		{
			$query->where(['TABLE_FOR_WORDS.nameTranslit' => $id])
				->orderBy(['dateCreated' => SORT_ASC]); // сортировка на случай, если есть 2 слова с одинаковым транслитом. Показываем более старое, так как вероятнее всего, что оно отмодерировано.
		}
		else
		{
			$query->where(['TABLE_FOR_WORDS.id' => (int) $id]);
		}

		$word = $query->one();

		if ($word != null)
		{
			if ($word->authorId > 0)
			{
				$author = User::findOne(['id' => $word->authorId]);
				$word->authorProfileLink = $author->profileLink;	
			}

			$session	= Yii::$app->session;
			$wordsSeen	= $session->get('wordsSeen', []);

			if (in_array($src, ['view']))
			{
				// запись в сессию уже просмотренных слов, чтобы в соседях каждый раз были новые слова
				if (!in_array($word->id, $wordsSeen))
				{
					$wordsSeen[] = $word->id;
				}

				$session->set('wordsSeen', $wordsSeen);

				/*
				 * Запись в таблицу просмотренных слов
				 * Нужно для того, чтобы показывать количество новых слов для текущего юзера в списке тэгов
				 * Запись только для залогинившихся юзеров
				 */
				if (!Yii::$app->user->isGuest)
				{
					$userId		= Yii::$app->user->identity->id;
					$viewExists = WordView::find()->where(['userId' => $userId, 'wordId' => $word->id])->exists();

					if (!$viewExists)
					{
						$wordView			= new WordView();
						$wordView->date		= new Expression('NOW()');
						$wordView->userId	= $userId;
						$wordView->wordId	= $word->id;
						$wordView->save();
					}
				}
			}

			// определение слов-соседей
			if (in_array($src, ['view']))
			{
				$randomSiblingIds = self::detectSiblings($word->id, $wordsSeen, $src);

				if (!empty($randomSiblingIds))
				{
					$query = static::find()
						->select(['id', 'name', 'nameTranslit'])
						->where(['in', 'id', $randomSiblingIds])
						->orderBy([new Expression('FIELD (id, ' . implode(', ', $randomSiblingIds) . ')')]);

					$word->randomSiblings = $query->all();
				}
			}	
		}

		return $word;
	}

	

	public function getWords($filterType = null, $filter = null, $offset = 0, $limit = 10)
	{
		$query = static::find()
			->joinWith([
				'tags',
			])
			->groupBy('TABLE_FOR_WORDS.id')
			->orderBy(['TABLE_FOR_WORDS.dateCreated' => SORT_DESC, 'TABLE_FOR_WORDS.name' => SORT_ASC])
			->offset($offset)
			->limit($limit)
			;

		switch ($filterType)
		{
			case 'tag':
				$query->where('TABLE_FOR_TAGS.sortName = :sortName', [':sortName' => $filter]);
				break;

			case 'search':
				$q = Yii::$app->request->get('q');
				$wordIdsRelevant = [];

				include(YII_ENV == 'dev' ? 'C:/local/path/to/sphinxapi.php' : '/path/to/sphinxapi.php');

				$cl = new \SphinxClient();
				$cl->SetServer('IP_ADDRESS', 'PORT');
				$result = $cl->Query($q);

				if ($result === false)
				{
					if (YII_ENV == 'dev')
					{
						echo "Query failed: " . $cl->GetLastError() . ".\n";
					}
				}
				else
				{
					if ($cl->GetLastWarning())
					{
						if (YII_ENV == 'dev')
						{
							echo "WARNING: " . $cl->GetLastWarning();
						}
					}

					if (!empty($result["matches"]))
					{
						foreach ($result["matches"] as $product => $info)
						{
							$wordIdsRelevant[] = $product;
						}
					}
				}

				$wordIdsRelevant = array_slice($wordIdsRelevant, $offset, $limit);
				$query->where(['in', 'TABLE_FOR_WORDS.id', $wordIdsRelevant])
					->offset(null)
					->limit(null);

				break;

			case 'popular':
				$query->orderBy(['TABLE_FOR_WORDS.popularity' => SORT_DESC, 'TABLE_FOR_WORDS.name' => SORT_ASC]);
				break;
		}
		
		return $query->all();
	}

	public static function detectSiblings($singleWordId = 0, $excludeWordIds = [], $src = null)
	{
		// доработать эту функцию. На небольших объемах данных рандомизация "на лету" не тормозит, но неизвестно, что будет, когда количество слов разрастется.

		// генерация соседей происходит для каждого слова в отдельности после его создания. Если что-то пошло не так, то можно сгенерировать соседей всем словам, если вызвать эту функцию без параметра, но это будет долго.
		$singleWordId = (int) $singleWordId;

		/*
		 * Похожие слова для листалки выбираются по следующему принципу
		 * 1) Совпадают все 3 тэга
		 * 2) Совпадают 2 самых редких тэга
		 * 3) Совпадает 1 самый популярный тэг
		 */
		$combinatorics	= new MathCombinatorics();

		// формирование комбинаций тегов и принадлежащих им слов
		$tagHouse	= [];
		$wordTagIds	= [];

		$wtjQuery = WordTag::find()
			->from('TABLE_FOR_WORD_TAGS as wt')
			->orderBy('RAND()')
			->limit(10000);

		// изменения при генерации для конкретного слова
		if ($singleWordId > 0)
		{
			$singleWord			= Word::findOne($singleWordId);
			$singleWordTagIds	= WordTag::find()->select('tagId')->where(['wordId' => $singleWordId])->asArray()->column();

			if (empty($singleWordTagIds))
			{
				return [];
			}

			$wtjQuery->where(['not in', 'wordId', array_diff($excludeWordIds, [$singleWordId])])
				->andWhere(['in', 'tagId', $singleWordTagIds])
				->limit(null);
		}

		$wordTagsJunc = $wtjQuery->asArray()->all();

		foreach ($wordTagsJunc as $junk)
		{
			$wordTagIds[$junk['wordId']][] = $junk['tagId'];
		}

		foreach ($wordTagIds as $wordId => $tagIds)
		{
			$combos = [];

			sort($tagIds);

			for ($i = count($tagIds); $i >= 0; $i--)
			{
				$combos = array_merge($combos, $combinatorics->combinations($tagIds, $i));
			}

			foreach ($combos as $combo)
			{
				$tagHouse[implode('-', $combo)][] = $wordId;
			}
		}

		// определение "соседей" для каждого слова по тэгам
		$tags = Tag::find()->select(['id', 'wordCount'])->asArray()->all();
		$tags = ArrayHelper::map($tags, 'id', 'wordCount');

		$wordSiblings = [];

		// изменения при генерации для конкретного слова
		if ($singleWordId > 0)
		{
			$wordTagIds = [$singleWordId => $wordTagIds[$singleWordId]];
		}

		foreach ($wordTagIds as $wordId => $tagIds)
		{
			if (!isset($wordSiblings[$wordId]))
			{
				$wordSiblings[$wordId] = [];
			}

			sort($tagIds);

			$combos	= [];

			/* Комбинации тэгов у слова делятся на 3 группы
			 * №3 группа - все 3 тэга слова, она приоритетна для происка похожих слов
			 * №2 группа - комбинации по 2 тэга. Нужно определить наиболее редкую связку и по ней найти соседей. Если найдено недостаточно слов, то продолжаем поиск по чуть более популярной связке
			 * №1 группа - ищем слова по 1 тэгу
			 */
			for ($i = count($tagIds); $i > 0; $i--)
			{ 
				$combos[$i] = $combinatorics->combinations($tagIds, $i);
			}

			// Сортировка комбо по редкости
			foreach ($combos as $comboNum => $comboVariants)
			{
				if (count($comboVariants) > 1)
				{
					$variantsChart = []; // массив распределения связок тегов по редкости, начиная с самых редких

					foreach ($comboVariants as $cvKey => $variant)
					{
						$varRarity = array_reduce($variant, function($res, $item) use ($tags) {
							$res += $tags[$item];
							return $res;
						});

						// в случае, если редкость одной комбинации совпадает с редкостью другой, то для последней комбинации редкость понижается по 1 баллу, пока не найдется редкость, которой еще нет.
						while (isset($variantsChart[$varRarity]))
						{
							--$varRarity;
						}

						$variantsChart[$varRarity] = $variant;
					}

					ksort($variantsChart);

					$combos[$comboNum] = array_values($variantsChart);
				}
			}

			$siblingsNeed	= 5;
			$siblingsAdded	= []; // массив для учета id всех соседей, которые проходят отсев. Нужен на случай, когда одно и то же слово пытается пролезть по разным уровням редкости (1, 2 или 3). К примеру у слова с соседом совпадают 3 тэга, а значит и 2 тэга и 1 тэг, сосед создаст свой дубликат и он выйдет на странице слова в разделе "Листать"
			
			// всеми немыслимыми способами надо сначала набрать в соседей слова по входному тэгу, а если уж таких нет, то уходим в другие тэги
			$tagEnterPoint = Yii::$app->session->get('tagEnterPoint', null);

			foreach ($combos as $comboNum => $comboVariants)
			{
				foreach ($comboVariants as $comboVariant)
				{
					if ($src == 'view' && $tagEnterPoint != null && !in_array($tagEnterPoint, $comboVariant))
					{
						continue;
					}

					if ($siblingsNeed == 0)
					{
						break 2;
					}

					$comboStr		= implode('-', $comboVariant);
					$comboSiblings	= array_diff($tagHouse[$comboStr], [$wordId]); // разница в массивах, чтобы исключить из соседей ID самого слова
					$comboSiblings	= array_diff($comboSiblings, $siblingsAdded); // фильтр на уже добавленных соседей, чтобы не пролез дубликат
					$comboSiblings	= array_slice($comboSiblings, 0, $siblingsNeed);
	
					//$wordSiblings[$wordId][$comboNum]	= $comboSiblings;
					$wordSiblings[$wordId]	= array_merge($wordSiblings[$wordId], $comboSiblings);
					$siblingsAdded			= array_merge($siblingsAdded, $comboSiblings); // учет уже добавленных соседей

					$siblingsNeed -= count($comboSiblings);
				}
			}
		}

		// если все еще нужно 5 соседей, то нужно очистить входной тэг
		if ($src == 'view' && $siblingsNeed == 5)
		{
			Yii::$app->session->set('tagEnterPoint', null);
		}

		if ($singleWordId > 0)
		{
			$wordSiblings = $wordSiblings[$singleWordId];

			// выборка левых соседей, когда 5 соседей не набирается по тэгам слова
			if ($siblingsNeed > 0)
			{
				// сначала добавляем соседей такой же части речи
				$addWordSiblings = Word::find()
					->select('id')
					->where(['not in', 'id', array_merge($wordSiblings, [$wordId])])
					->andWhere(['partOfSpeech' => $singleWord->partOfSpeech])
					->orderBy('RAND()')
					->limit($siblingsNeed)
					->column();
				$wordSiblings = array_merge($wordSiblings, $addWordSiblings);
				$siblingsNeed -= count($addWordSiblings);

				// если и тут недостаточно соседей, то выбираем соседей вообще по рандому
				if ($siblingsNeed > 0)
				{
					$addWordSiblings = Word::find()
						->select('id')
						->where(['not in', 'id', array_merge($wordSiblings, [$wordId])])
						->orderBy('RAND()')
						->limit($siblingsNeed)
						->column();
					$wordSiblings = array_merge($wordSiblings, $addWordSiblings);
				}
			}

			return $wordSiblings;
		}
		else
		{
			return $wordSiblings;
		}
	}

	public static function exists($wordId = 0)
	{
		return static::find()->where(['id' => $wordId])->exists();
	}
    
    public function getPreparedMetaKeywords()
    {
        if ($this->metaKeywords != null)
        {
            $metaKeywords = explode(' ', $this->metaKeywords);
            
            if (!empty($metaKeywords))
            {
                $metaKeywords = array_unique($metaKeywords);
                    
                return implode(', ', $metaKeywords);
            }
        }
        
        return null;
    }
    
    public static function countPopularity()
	{
		$query = static::find()
			->from('TABLE_FOR_WORDS as w')
			->select(['w.id', 'w.dateCreated', 'COUNT(wv.wordId = w.id) - 1 as viewCount'])
			->leftJoin('TABLE_FOR_WORD_VIEWS AS wv', 'wv.wordId = w.id')
			->groupBy(['w.id'])
			;

		$words = $query->all();

		foreach ($words as $k => $v)
		{
			// извращение, ибо старый аттрибут viewCount не апдейтится, если значение нового такое же, поэтому сначала старый ставится на 1 меньше (в выборке из БД), а новый на 1 больше, то есть возвращается реальное значение
			$v->viewCount = $v['viewCount'] + 1;

			// +1 день, чтобы не делить на 0
			$v->popularity = round($v['viewCount'] / (Helpers::dateDiff($v['dateCreated'], 'days') + 1), 4);
			
			$v->updateAttributes(['viewCount', 'popularity']);
		}
	}
}
