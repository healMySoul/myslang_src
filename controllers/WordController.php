<?php
namespace app\controllers;

use Yii;
use yii\bootstrap\ActiveForm;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\HttpException;
use yii\web\Response;

use app\models\CustomData;
use app\models\Tag;
use app\models\Word;

class WordController extends Controller
{
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'only' => ['add-definition'],
				'rules' => [
					[
						'allow' => true,
						'actions' => ['add-definition'],
						'roles' => ['@'],
					],
				],
			]
		];
	}

	public function actionIndex()
	{
		return $this->render('index');
	}

	public function actionEdit($id = 0)
	{
		$model = new Word();

		if ($id > 0)
		{
			$model = $model->getWord($id);

			if ($model == null)
			{
				throw new HttpException(404, 'Слово не найдено.');
			}

			if (!Yii::$app->user->can('editWord', ['word' => $model]))
			{
				return $this->goHome();
			}
		}

		if (Yii::$app->request->isAjax && $model->load(Yii::$app->request->post()))
		{
			Yii::$app->response->format = Response::FORMAT_JSON;

			return ActiveForm::validate($model);
		}

		$model->scenario = 'createByUser';

		if ($model->load(Yii::$app->request->post()))
		{
			if ($word = $model->saveWord())
			{
				return $this->redirect(['word/view', 'nameTranslit' => $word->nameTranslit]);
			}
		}
		
		return $this->render('edit', [
			'model' => $model,
		]);
	}

	public function actionList($filterType = null, $filter = null)
	{
		$title = null;

		if ($filterType == null)
		{
			$tags		= Tag::mainList('words');
			$words		= [];
			$filterLit	= null;
		}
		else
		{
			$page	= (int) Yii::$app->request->get('page', 1);
			$limit	= 10;
			$offset	= ($page - 1) * $limit;

			$tags		= [];
			$word		= new Word();
			$words		= $word->getWords($filterType, $filter, $offset, $limit);
			$filterLit	= $filter;

			switch ($filterType)
			{
				case 'tag':
					$tag = Tag::find()->select(['id', 'name'])->where(['sortName' => $filter])->one();

					if ($tag != null)
					{
						$filterLit = $tag->name;

						// запись в сессию тэга входа. Нужно для того, чтобы в похожих словах сначала прокрутить все слова по этому тэгу, а потом уже уходить в дебри других слов.
						Yii::$app->session->set('tagEnterPoint', $tag->id);
					}
					break;
				
				case 'search':
					$filterLit = Yii::$app->request->get('q');
					break;

				case 'new':
					$title = 'Новые слова. Словарь современного языка';
					break;
			}
		}

		// запись в сессию "точки входа", для кнопки "назад" в мобильной версии
		if ($filterType != null)
		{
			Yii::$app->session->set('wordEnterPoint', Url::current());
		}
        
        // ajax-подгрузка ленты слов
		if (Yii::$app->request->isAjax)
		{
			$res		= ['state' => 'err'];
			$wordsHtml	= [];

			if(!empty($words))
			{
				foreach ($words as $word)
				{
					$wordsHtml[] = $this->renderPartial('word', ['word' => $word]);
				}

				$res['state']		= 'ok';
				$res['wordsHtml']	= $wordsHtml;
			}

			exit(json_encode($res));
		}

		return $this->render('list', [
			'title'			=> $title,
			'filterType'	=> $filterType,
			'filter'		=> $filter,
			'filterLit'		=> $filterLit,
			'q'				=> Yii::$app->request->get('q'),
			'tags'			=> $tags,
			'words'			=> $words,
			'asideList'		=> CustomData::asideList('tests'),
		]);
	}

	public function actionView($nameTranslit = null)
	{
		$word = new Word();
		$word = $word->getWord($nameTranslit, 'view');

		if ($word == null)
		{
			throw new HttpException(404, 'Слово не найдено.');
		}

		// определение ID следующего слова для кнопки вперед в мобильной версии
		$nextWordNameTranslit = '';
		if (!empty($word->randomSiblings))
		{
			$firstSibling			= reset($word->randomSiblings);
			$nextWordNameTranslit	= $firstSibling->nameTranslit;
		}
		Yii::$app->session->set('nextWordNameTranslit', $nextWordNameTranslit);

		$data = [
			'word'		 => $word,
			'asideList'  => CustomData::asideList('wordView', ['word' => $word]),
		];

		return $this->render('view', $data);
	}

	public function actionAddDefinition()
	{
		# few lines of code were here
	}
}