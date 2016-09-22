<?php

namespace app\commands;

use Yii;
use yii\console\Controller;

use app\models\Emailing;
use app\models\Word;

ini_set('display_errors', 'On');
ini_set('memory_limit', -1);
ini_set('max_execution_time', 3600);

class CronController extends Controller
{
	public function actionDay()
	{
		$sleep = 5;

		// подсчет популярности слов (просмотры / время)
		Word::countPopularity(); sleep($sleep);

		// лог
		file_put_contents(dirname(__FILE__) . '/../log/cron_log_day.txt', "\r\n" . date("d.m.y H:i:s"), FILE_APPEND | LOCK_EX);
	}

	public function actionMinute()
	{
		$sleep = 5;

		// отправка почты раз в минуту
		Emailing::sendQueuedEmails(); sleep($sleep);

		// лог
		file_put_contents(dirname(__FILE__) . '/../log/cron_log_minute.txt', "\r\n" . date("d.m.y H:i:s"), FILE_APPEND | LOCK_EX);
	}
}
