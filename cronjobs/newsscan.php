<?php
	require_once("config.php");

	file_put_contents("newsscan.log", date('c') . " Newsc-Scan started\n", FILE_APPEND);

	function ignoreWord($word) {
		$blacklist = array(
			"sich",
			"halbiert",
			"hat",
			"der",
			"die",
			"in",
			"von",
			"ein",
			"auf",
			"gegen",
			"fr",
			"vor",
			"ist",
			"wird",
			"nicht",
			"das",
			"mit",
			"sich",
			"wir",
			"im",
			"und",
			"an",
			"als",
			"zu",
			"des",
			"eine",
			"neue",
			"mehr",
			"für",
			"aus",
			"um",
			"will",
			"zum",
			"am",
			"über",
			"dem",
			"ab",
			"sie",
			"bei",
			"zurück",
			"jetzt",
			"was",
			"alle",
			"wie",
			"neuer",
			"war",
			"nur",
			"noch",
			"er",
			"so",
			"einen",
			"viele",
			"haben",
			"den",
			"wenn",
			"es",
			"seinen",
			"ihren",
			"seiner",
			"vom",
			"nach",
			"da",
			"fort",
			"of",
			"the",
			"on",
			"and",
			"wegen",
			"ja",
			"nein",
			"sind",
			"auch",
			"sein",
			"wer",
			"zwei",
			"eins",
			"drei",
			"ihr",
			"bis",
			"doch",
			"ohne",
			"ich",
			"zur",
			"seine",
			"uns",
			"man",
			"mir",
			"du",
			"unter",
			"bin",
			"seit",
			"kann",
			"kein",
			"soll"
		);

		return in_array($word, $blacklist);
	}

	header("Content-Type: text/html; charset=utf-8");
	mb_internal_encoding('UTF-8');

	$apiLink = "api.zeit.de/content?q=department:wirtschaft%20OR%20department:politik%20OR%20department:digital&limit=1000&sort=release_date%20desc&fields=title&api_key=a374ba6ae49faeb3af267874fb185392914670071e2b14b1a067";
	$curl = curl_init( $apiLink );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($curl);
	$data = json_decode($res);

	$newswords = array();

	foreach($data->matches as $news) {
		$tmpWords = explode(" ", $news->title);
		foreach($tmpWords as $word) {
			$word = preg_replace("/[\,\.\-\"\'?!]/", '', mb_strtolower($word));
			//$word = preg_replace("/[^A-Za-z0-9öäüß ]/", '', mb_strtolower($word));
			if( !ignoreWord($word) ) {
				if( isset($newswords[$word]) ) {
					$newswords[$word]++;
				}
				else {
					$newswords[$word] = 1;
				}
			}
		}
	}

	$scores = array();
	$db = new DatabaseManager();
	$db->connect();
	$res = $db->query("SELECT id,title FROM contest.item");
	$db->query("TRUNCATE TABLE contest.newsscore");

	foreach( $res as $row ) {
		$itemwords = explode(" ", $row["title"]);
		$score = 0;

		foreach( $itemwords as $word ) {
			$word = preg_replace("/[\,\.\-\"\'?!]/", '', mb_strtolower($word));
			//echo $word."<br>";
			if( isset($newswords[$word]) ) {
				$score += $newswords[$word];
			}
		}
		$score = $score/count($itemwords);
		$scoreModel = new NewsScore();
		$scoreModel->item = $row["id"];
		$scoreModel->score = $score;
		$scoreModel->save();

		//echo $row["id"]." - ".$score;
	}

	asort($newswords);
	foreach($newswords as $word => $count) {
		echo $word." - ".$count.PHP_EOL;
	}

	file_put_contents("newsscan.log", date('c') . " Newsc-Scan finished\n", FILE_APPEND);

?>