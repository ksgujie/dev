<?php namespace App\Http\Controllers;
use App\Libs\Requests;
use App\Singer;

set_time_limit(0);

class SongController extends Controller {

	public function getGet($id=0) {

//		$rs = Singer::all();
//		$t=[];
//		foreach ($rs as $r) {
//			$t[]=$r->id;
//		}
//		file_put_contents('g:/id.txt', join("\n", $t));
//		die;

		$singer = Singer::where('id', $id)->first();
		$name = $singer->姓名;
		$headers = [
			'User-Agent'=>'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7',
		];
		$response = Requests::get("http://music.baidu.com/search?key=$name", $headers);
		$artist = regMatch($response->body, '|data-url="/artist/(\d+)/|i');
		$singer->artist = $artist;
		$singer->save();

		file_put_contents("f:/songs/".$id.'.html', $response->body);
		file_put_contents("g:/a/$id.txt", $artist."\n".$name);

		return "$id - $artist - $name";
	}


}