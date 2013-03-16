<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 10:36 AM
 * To change this template use File | Settings | File Templates.
 */

require_once("config.php");

$feeds = array(
	'http://www.heise.de/newsticker/heise.rdf',
	'http://rss.bild.de/bild-news.xml',
	'http://rss.sueddeutsche.de/rss/Eilmeldungen',
	'http://rss.feedsportal.com/795/f/449002/index.rss',
	'http://www.tagesschau.de/xml/rss2/',
	'http://www.welt.de/?service=Rss',
	'http://www.spiegel.de/schlagzeilen/index.rss',
	'http://www.faz.net/rss/aktuell/',
	'http://feeds.n24.de/n24/homepage?format=xml',
	'http://www.n-tv.de/rss',
	'http://newsfeed.zeit.de/index',
	'http://rss.feedsportal.com/c/429/f/646647/index.rss',
	'http://www.merkur-online.de/aktuelles/welt/rssfeed.rdf',
	'http://www.netzeitung.de/rss/Titelseite'
);

$reader = new RssReader();
$extractor = new BuzzwordExtractor();

echo "Reading feeds:".PHP_EOL;

foreach($feeds as $feed) {
	echo "Touchung ".$feed."...".PHP_EOL;
	$data = $reader->read($feed);

	foreach($data as $d) {
		$extractor->addString($d['title'], 5);
		$extractor->addString( strip_tags($d['description']) );
	}
}

echo PHP_EOL."Extracting buzzwords...".PHP_EOL;
$buzzwords = $extractor->extract();

$scores = array();
$db = new DatabaseManager();
$db->connect();
$res = $db->query("SELECT id,title FROM contest.item");
$db->query("TRUNCATE TABLE contest.newsscore");

echo "Creating scores...".PHP_EOL;
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

echo "Sorting for log...".PHP_EOL;
asort($buzzwords);
$out = "";

foreach( $buzzwords as $buzzword => $count ) {
	$out .= $buzzword." - ".$count.PHP_EOL;
}

echo "Logging...".PHP_EOL;
file_put_contents(LOG_PATH."newswords", "Words at date('c'):\n-----------------------\n".$out."\n\n", FILE_APPEND);
echo "Finished!".PHP_EOL;

