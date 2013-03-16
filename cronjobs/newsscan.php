<?php
	require_once("../config.php");

	file_put_contents("newsscan.log", date('c') . " Newsc-Scan started\n", FILE_APPEND);

	header("Content-Type: text/html; charset=utf-8");
	mb_internal_encoding('UTF-8');

	$apiLink = "api.zeit.de/content?q=department:wirtschaft%20OR%20department:politik%20OR%20department:digital&limit=1000&sort=release_date%20desc&fields=title&api_key=a374ba6ae49faeb3af267874fb185392914670071e2b14b1a067";
	$curl = curl_init( $apiLink );
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	$res = curl_exec($curl);
	$data = json_decode($res);

	$extractor = new BuzzwordExtractor();

	foreach($data->matches as $news) {
		$extractor->addString($news->title);
	}
	$newswords = $extractor->extract();

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