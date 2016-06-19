<?php namespace App\Http\Controllers;
use App\Libs\Requests;
use App\Singer;

set_time_limit(0);

class SingerController extends Controller {

	public function getFacelist()
	{
		$singers = Singer::all();
		$fo=fopen('g:/a.txt', 'wb');
		foreach ($singers as $s) {
			fwrite($fo, $s->face."\n");
		}
		fclose($fo);
	}

	public function getFace()
	{
		for ($i=0; $i < 500; $i++) { 
			$singer = Singer::where('ok',1)->first();
			if (!$singer) {
				exit('完成');
			}
			$html = file_get_contents("f:/singer/".$singer->id.'.html');
			$face = regMatch($html, '|http://cst1.youqike.com/bbs/source/plugin/nicemusic/singer/t/\d+\.jpg|i');
			$singer->face = $face;
			$singer->ok=2;
			$singer->save();

			echo $face."\n";
		}
	}

	public function getGetlist($page=1) {
		$maxPage = 486;
		if ($page > $maxPage) {
			die('完成');
		}

		$url = "http://cst1.youqike.com/bbs/nicemusic/singer/all.html?page=$page";
		$response = Requests::get($url);
		preg_match_all('|http://cst1\.youqike\.com/bbs/nicemusic/singer/detail/sid/[a-z0-9]+\.html|', $response->body, $_arrUrl);
		$arrUrls = array_unique($_arrUrl[0]);
		foreach ($arrUrls as $url) {
			if (Singer::where('来源', $url)->count() == 0) {
				$singer = new Singer();
				$singer->来源 = $url;
				$singer->save();

				echo $url.'<br>';
			}
		}
	}

	public function getContent()
	{
		$singer = Singer::where('ok',0)->first();
		if (!$singer) {
			exit('完成');
		}

		$response = Requests::get($singer->来源);
		file_put_contents('f:/singer/'.$singer->id.'.html', $response->body);

		$arrRegx['姓名'] = '/<th>姓名：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['首字母'] = '/<th>名称首字母:<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['拼音'] = '/<th>名称拼音:<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['标签'] = '/<th>标签：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['性别'] = '/<th>性别：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['别名'] = '/<th>别名：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['国籍'] = '/<th>国籍：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['语言'] = '/<th>语言：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['出生地'] = '/<th>出生地：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['生日'] = '/<th>生日：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['星座'] = '/<th>星座：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['身高'] = '/<th>身高：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['体重'] = '/<th>体重：<\/th>\s+<td>(.*?)<\/td>/is';
		$arrRegx['简介'] = '/<th valign="top">简介：<\/th>\s+<td>(.*?)<\/p>/is';

		foreach ($arrRegx as $k=>$_regx) {
			$v = regMatch($response->body, $_regx);
			$singer->$k = clearHtml($v);
		}
		$singer->ok=1;
		$singer->save();

		echo $singer->id;
	}

}