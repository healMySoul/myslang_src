<?php

namespace app\controllers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\web\Controller;

use app\models\Tag;

class AjaxController extends Controller
{
	public function actionAutocomplete()
	{
		$source = Yii::$app->request->get('source');
		$term = Yii::$app->request->get('term');
		$limit = (int) Yii::$app->request->get('limit') > 0 ? (int) Yii::$app->request->get('limit') : 10;
		
		switch ($source)
		{
            // получение списка тэгов при создании или редактировании слова
			case 'wordEditTags':
				$data = Tag::find()
					->select('name')
					->where('name LIKE :name', [':name' => '%' . $term . '%'])
					->limit($limit)
					->orderBy('name')
					->all();
				$data = ArrayHelper::getColumn($data, 'name');
				break;

			default:
				$data = [];
				break;
		}
		
		return json_encode($data);
	}

	public function actionGetData()
	{
		$res = ['state' => 'err'];

		$type = Yii::$app->request->get('type');

		switch ($type)
		{
			case 'checkWordExistence':
				$name = Yii::$app->request->get('name');
				$res['exists'] = (bool) Word::findOne(['name' => $name]);
				$res['state'] = 'ok';
				sleep(1);
				break;
			
			default:
				break;
		}

		return json_encode($res);
	}

	public function actionPostData()
	{
		$res		= ['state' => 'err'];
		$request	= Yii::$app->request;
		$type		= $request->post('type');

		switch ($type)
		{
			default:
				break;
		}

		return json_encode($res);
	}
}
