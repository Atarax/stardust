<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Itembuzzword started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$items = $db->query("
				SELECT DISTINCT id, title, text FROM contest.item WHERE recommendable > 0 IS NOT NULL;
			");

$db->query("TRUNCATE TABLE contest.itembuzzword");
$extractor = new BuzzwordExtractor();

foreach( $items as $i => $item ) {
	echo "Item $i (".$item["id"].") of ".count($items)."\n";

	$extractor->addString($item["title"], 3);
	$extractor->addString($item["text"], 1);

	$buzzwords = $extractor->extract();

	$query = "INSERT INTO contest.itembuzzword(item, buzzword, count) VALUES ";

	$i = 0;

	foreach( $buzzwords as $buzzword => $count ) {
		if( $count < 2 ) {
			continue;
		}
		$query .= ( $i == 0 ? "" : ",")."(".$item["id"].",'".mysql_real_escape_string($buzzword)."',".$count.")";
		$i++;
	}
	if( $i > 0 ) {
		$db->query($query);
	}

	$extractor->reset();
}

echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Itembuzzword finished\n", FILE_APPEND);

?>