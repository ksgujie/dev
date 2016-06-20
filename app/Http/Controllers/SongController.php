<?php namespace App\Http\Controllers;
use App\Libs\Requests;
use App\Singer;
use App\Song;

set_time_limit(0);

class SongController extends Controller {

	public function getDetail($song_id)
	{
		$song = Song::where('id',$song_id)->first();
		$baiduid = $song->歌号;
		$url="http://music.baidu.com/song/$baiduid/";
		$headers = [
			'User-Agent'=>'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7',
		];
		$resp = Requests::get($url, $headers);
		$song->专辑封面 = regMatch($resp->body, '|<div class="cover"><img alt="" src="(.+?)">|i');
		$gc = regMatch($resp->body, '/<div class="lrc-panel">(.*?)<\/div>/is');
		$gc = preg_replace('/\[.+?\]/', '', $gc);
		$gc = htmlspecialchars_decode($gc, ENT_QUOTES);
		$song->歌词 = trim($gc);
		$song->专辑 = regMatch($resp->body, '/<div class="btn-opt btn-album album need-active url log" data-title="(.+?)"/i');
		$song->专辑号 = regMatch($resp->body, '|data-url="#/album/(\d+)/"|i');

		$song->ok=1;
		$song->save();

		file_put_contents("f:/song-detail/$song_id.html", $resp->body);

		return $song_id . ' ' . $song->歌名;
	}
	//列表
	public function getGet($singer_id)
	{
		$headers = [
			'User-Agent'=>'Mozilla/5.0 (iPhone; U; CPU iPhone OS 4_0 like Mac OS X; en-us) AppleWebKit/532.9 (KHTML, like Gecko) Version/4.0.5 Mobile/8A293 Safari/6531.22.7',
		];

		$singer = Singer::where('id', $singer_id)->first();
		$artist = $singer->artist;
		$url = "http://music.baidu.com/loadmore/artistsong/?limits=1000&order=2&method=baidu.ting.artist.getSongList&tinguid=$artist&offset=0";
		$resp = Requests::get($url, $headers);
		$json = json_decode($resp->body);
//		dd($json->repeator);
		preg_match_all('|data-url="/song/(\d+)/">\s+<div class="left">(.+?)</div>\n|is', $json->repeator, $arr);
		for ($i = 0; $i < count($arr[0]); $i++) {
			$歌号 = $arr[1][$i];
			if (Song::where('歌号', $歌号)->count() == 0) {
				$song = new Song();
				$song->singer_id = $singer_id;
				$song->歌号 = $歌号;
				$song->歌名 = trim($arr[2][$i]);
				$song->save();

				echo $song->歌名;
				echo "\n";
			}
		}

		$singer->ok=1;
		$singer->save();

		file_put_contents("f:/song-id/$singer_id.html", $resp->body);

	}

	public function getArtist($id=0) {

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