<?php
	
function validFileName($fname,$flag='')
{
	$s='\/:*?"<>|';
	for ($i=0; $i<strlen($s); $i++) {
		$fname=str_replace($s[$i],$flag,$fname);
	}
	return $fname;
}//validFileName

function getip ()
{
	global $_SERVER;
	if (getenv('HTTP_CLIENT_IP')) {
		$ip = getenv('HTTP_CLIENT_IP');
	} else if (getenv('HTTP_X_FORWARDED_FOR')) {
		$ip = getenv('HTTP_X_FORWARDED_FOR');
	} else if (getenv('REMOTE_ADDR')) {
		$ip = getenv('REMOTE_ADDR');
	} else {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	return $ip;
}


function getTagData($str, $start, $end){
	if (!empty($start)) {
		$str = explode($start, $str, 2);
		$str = $str[1];
	}
	if (!empty($end)) {
		$str = explode($end, $str, 2);
		$str = $str[0];
	}
	return $str;
}

function urljoin($baseurl,$url)
{
	if (!preg_match('/\/$/',$baseurl))
	$baseurl = substr($baseurl,0,strlen($baseurl)-strlen(basename($baseurl))-1);
	else
	$baseurl = substr($baseurl,0,strlen($baseurl)-1);
	$arrurl = explode('/',str_replace('http://','',$baseurl));
	$arrurl[0] = 'http://'.$arrurl[0];

	$v=trim($url);
	#http://
	if (preg_match("/^(http|https|ftp)?:\/\//i",$v))
	$result = $v;
	#/demo.gif
	elseif (preg_match("/^\//",$v))
	$result = $arrurl[0].$v;
	#demo.gif
	elseif (preg_match("/^[\w-]/",$v))
	$result = $baseurl.'/'.$v;
	#./demo.gif
	elseif (preg_match("/^\.\//",$v))
	$result = $baseurl.'/'.substr($v,2,strlen($v)-1);
	#../
	elseif (preg_match("/^[\.\.\/]+/",$v))
	{
		$ar = explode('../',$v);
		$upperPathCount = count($ar)-1;
		$ar2 = $arrurl;
		for ($i=0; $i<$upperPathCount; $i++)
		array_pop($ar2);
		$result = join('/',$ar2).'/'.$ar[count($ar)-1];
	}
	return $result;
}

function regMatch($str, $regStr, $striphtml=true)
{
	if (preg_match($regStr, $str, $result)) {
		if (isset($result[1])) {
			$r= trim($result[1]);
		} else {
			$r= trim($result[0]);
		}
		$rr= $striphtml ? strip_tags($r) : $r;
		return trim($rr);
	} else {
		return null;
	}
}


function clearHtml($html)
{
	$html=preg_replace('/<script.*?<\/script>/is','',$html);
	$html=preg_replace('/<iframe.*?<\/iframe>/is','',$html);
	$html=preg_replace('/[\r|\n]/', '', $html);
	$html=str_ireplace(['<br/>','<br />','<br>','</p>'], "\n", $html);
	$html=trim($html);
	return $html;
}//clear

/**
 * @param $arrData 二维数组
 * @param $saveFile 保存的文件名
 * @param bool $overWriteExistFile 是否覆盖已存在的文件
 * @throws PHPExcel_Exception
 */
function arrayToExcel($arrData, $saveFile, $overWriteExistFile = false) {
	$objExcel = new PHPExcel();
	$objSheet = $objExcel->getActiveSheet();
	$objSheet->fromArray($arrData);
	$w = new \PHPExcel_Writer_Excel2007($objExcel);
	if (!$overWriteExistFile && file_exists($saveFile)) {
		exit($saveFile.' 文件已经存在，不能覆盖生成！');
	}
	$w->save($saveFile);
}

function utf8($s) {
	if (is_string($s)) {
		return mb_convert_encoding($s, 'utf-8', 'gbk');
	} elseif (is_array($s)) {
		foreach ($s as $k=>$v) {
			$s[$k] = mb_convert_encoding($v, 'utf-8', 'gbk');
		}
		return $s;
	}
}

function gbk($s) {
	if (is_string($s)) {
		return mb_convert_encoding($s, 'GBK', 'UTF-8');
	} elseif (is_array($s)) {
		foreach ($s as $k=>$v) {
			$s[$k] = mb_convert_encoding($v, 'GBK', 'UTF-8');
		}
		return $s;
	}
}

function pp($s) {
	echo '<pre>';
	print_r($s);
	echo '</pre>';
}

function pd($s) {
	pp($s);
	die;
}

function copedir( $source,$target ) {
	if(is_dir($source)){
		mkdir($target);
		$dir = dir($source);
		while ( ($f=$dir->read())!==false && $f!='.' && $f!='..' ){
			if (is_dir($source.'/'.$f)){
				copedir($source.'/'.$f,$target.'/'.$f);
			}else{
				copy($source.'/'.$f,$target.'/'.$f);
			}
		}
	}else{
		copy($source,$target);
	}
}

function getFilesListInDir($dir) {
	$hnd = opendir($dir);
	$r=null;
	while (($file = readdir($hnd)) !== false)
	{
		if (!in_array($file, ['.', '..'])) {
			$r[]=$file;
		}
	}
	closedir($hnd);
	return $r;
}

function configFilePath($filename)
{
	return base_path("配置文件/$filename");
}

function matchConfig($strKeys) {
	$keys = explode('.', $strKeys);
	$matchConfig = \App\Modules\MatchConfig\Config::read();
	$r=null;
	foreach ($keys as $key) {
		if (!$r) {
			if (!isset($matchConfig[$key])) {
				dump($strKeys);
//				pd("{$strKeys}：{$key} 不存在");
			}

			$r = $matchConfig[$key];
		} else {
			$r = $r[$key];
		}
	}
	return $r;
} 