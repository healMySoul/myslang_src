<?php
namespace app\components;

use Yii;

class Helpers
{
	public static function data($key)
	{
		$dataArray = [
			'wordEnd' => [
				'день' => [
					1 => 'день',
					2 => 'дня',
					3 => 'дней',
				],
				'новое' => [
					1 => 'новое',
					2 => 'новых',
					3 => 'новых',
				],
				'вопрос' => [
					1 => 'вопрос',
					2 => 'вопроса',
					3 => 'вопросов',
				],
			],
			'wordCase' => [
				'nominative' => [
					'name' 	=> 'именительный',
					'quest'	=> 'Что?',
				],
				'genitive' => [
					'name' 	=> 'родительный',
					'quest'	=> 'Нет чего?',
				],
				'dative' => [
					'name' 	=> 'дательный',
					'quest'	=> 'Кому, чему?',
				],
				'accusative' => [
					'name' 	=> 'винительный',
					'quest'	=> 'Вижу что?',
				],
				'ablative' => [
					'name' 	=> 'творительный',
					'quest'	=> 'Любуюсь чем?',
				],
				'prepositional' => [
					'name' 	=> 'предложный',
					'quest'	=> 'О чем?',
				],
			],
		];

		return isset($dataArray[$key]) ? $dataArray[$key] : [];
	}

	public static function ip2db($ip = null)
	{
		if ($ip == null)
	    {
	        $ip = self::userIP();
	    }
	    
	    return sprintf('%u', ip2long($ip));
	}

	public static function db2ip($ip)
	{
	    return long2ip($ip);
	}

	public static function userIP($toDb = true, $filterLocal = false)
    {
        $ipSources = [
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR',
        ];

        foreach ($ipSources as $key)
        {
            if (array_key_exists($key, $_SERVER) === true)
            {
                foreach (array_map('trim', explode(',', $_SERVER[$key])) as $ip)
                {
                    if ($filterLocal)
                    {
                        $checkFilter = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
                        
                        if ($checkFilter !== false)
                        {
                            return $toDb ? self::ip2db($ip) : $ip;
                        }
                    }
                    else
                    {
                        return $toDb ? self::ip2db($ip) : $ip;
                    }
                }
            }
        }

        return null;
    }

    public static function wordEnd($num, $word)
    {
    	$data = self::data('wordEnd');

		$num = (int) $num;
		
		$residue = $num % 100;

		if ($residue > 19)
		{
			$residue = $residue % 10;
		}

		switch ($residue)
		{
			case 1:
				$key = 1;
				break;

			case 2:
			case 3:
			case 4:
				$key = 2;
				break;
			
			default:
				$key = 3;
				break;
		}

		$res = isset($data[$word][$key]) ? ($num . ' ' . $data[$word][$key]) : '';
		
		return $res;
    }

    public static function array_shift_assoc(&$arr)
    {
		reset($arr);
		
		$return = [key($arr) => current($arr)];

		unset($arr[key($arr)]);

		return $return; 
	}

	public static function dateDiff($date, $units = 'seconds')
	{
	    $date     = new \DateTime($date);
	    $curDate  = new \DateTime(date('Y-m-d h:i:s'));
	    $dateDiff = $curDate->diff($date);

	    return $dateDiff->$units;
	}

	public static function wordCasesForInput()
	{
		$wordCase = self::data('wordCase');
		$cases = [];

		foreach (['singular', 'plural'] as $sp)
		{
			foreach ($wordCase as $case => $caseInfo)
			{
				$sub				= ucfirst(substr($case, 0, 3)) . 'Case';
				$cases[$sp][$sub]	= $caseInfo['quest'];
			}
		}

		return $cases;
	}

	public static function rus2translit($string, $reverse = false)
	{
	    $convertTable = array(
	        'а' => 'a',   'б' => 'b',   'в' => 'v',
	        'г' => 'g',   'д' => 'd',   'е' => 'e',
	        'ё' => 'e',   'ж' => 'zh',  'з' => 'z',
	        'и' => 'i',   'й' => 'i',   'к' => 'k',
	        'л' => 'l',   'м' => 'm',   'н' => 'n',
	        'о' => 'o',   'п' => 'p',   'р' => 'r',
	        'с' => 's',   'т' => 't',   'у' => 'u',
	        'ф' => 'f',   'х' => 'h',   'ц' => 'c',
	        'ч' => 'ch',  'ш' => 'sh',  'щ' => 'sch',
	        'ь' => '',    'ы' => 'y',   'ъ' => '',
	        'э' => 'e',   'ю' => 'yu',  'я' => 'ya',
	        
	        'А' => 'A',   'Б' => 'B',   'В' => 'V',
	        'Г' => 'G',   'Д' => 'D',   'Е' => 'E',
	        'Ё' => 'E',   'Ж' => 'Zh',  'З' => 'Z',
	        'И' => 'I',   'Й' => 'I',   'К' => 'K',
	        'Л' => 'L',   'М' => 'M',   'Н' => 'N',
	        'О' => 'O',   'П' => 'P',   'Р' => 'R',
	        'С' => 'S',   'Т' => 'T',   'У' => 'U',
	        'Ф' => 'F',   'Х' => 'H',   'Ц' => 'C',
	        'Ч' => 'Ch',  'Ш' => 'Sh',  'Щ' => 'Sch',
	        'Ь' => '',    'Ы' => 'Y',   'Ъ' => '',
	        'Э' => 'E',   'Ю' => 'Yu',  'Я' => 'Ya',
	    );

		if ($reverse)
		{
			$convertTable = array_flip($convertTable);
		}
	    
	    return strtr($string, $convertTable);
	}

	public static function translit2rus($string)
	{
	    return self::rus2translit($string, true);
	}

	public static function mb_ucfirst($str)
	{
    	return mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($str, 1, mb_strlen($str, 'UTF-8'), 'UTF-8');
	}

	public static function imgResize($path, $width, $height, $keepScale = true, $quality = 0, $enlarge = false)
	{
	    // quality:
	    // PNG: 0-9 (9 - максимальное сжатие)
	    // JPEG 0-100 (100 - максимальное качество)
	    // Если параметры $width и $height больше ширины и высоты изменяемого изображения, то увеличиваться оно будет только в том случае, если параметр $enlarge == true, иначе не будет никаких действий, ибо размеры изображения вписываются в требуемые.

	    //$fileList = is_dir($path) ? DirFileList($path) : (array) $path;
	    $fileList = (array) $path;

	    foreach ($fileList as $k => $filePath)
	    {
	        if (file_exists($filePath))
	        {
	            $img = new Yii::$app->simpleImg();
	            $img->load($filePath);
	            
	            if ($width > 0)
	            {
	                if ($width < $img->getWidth()
	                || ($width > $img->getWidth() && $enlarge))
	                {
	                    $img->resizeWidth($width, $keepScale);
	                }
	            }

	            if ($height > 0)
	            {
	                if ($height < $img->getHeight()
	                || ($height > $img->getHeight() && $enlarge))
	                {
	                    $img->resizeHeight($height, $keepScale);
	                }
	            }
	            
	            $img->save($filePath, $quality);
	        }
	    }
	}
}