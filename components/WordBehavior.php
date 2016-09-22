<?php

namespace app\components;

use yii\behaviors\AttributeBehavior;
use yii\db\ActiveRecord;
use app\models\Word;

class WordBehavior extends AttributeBehavior
{
	public function events()
	{
		return [
			ActiveRecord::EVENT_BEFORE_INSERT => 'beforeSave',
			//ActiveRecord::EVENT_BEFORE_UPDATE => 'beforeSave',
		];
	}

	public function beforeSave($event)
	{
		$nt = trim($this->owner->name);
		$nt = Helpers::rus2translit($nt);
		$nt = strtolower($nt);
		$nt = preg_replace('/\s+/', '-', $nt);
		$nt = preg_replace('/[^a-zA-Z0-9-]/', '', $nt);
		
		$this->owner->nameTranslit = $nt;
	}
}