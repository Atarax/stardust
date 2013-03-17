<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Similaritems started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$items = $db->query("
				SELECT id FROM contest.item WHERE recommendable > 0;
			");

$db->query("TRUNCATE TABLE contest.similaritems");
$extractor = new BuzzwordExtractor();

foreach( $clients as $i => $client ) {
	$items = $db->query("
		SELECT *, i1.count+i2.count AS similarity FROM itembuzzword i1, itembuzzword i2 WHERE i1.buzzword = i2.buzzword AND i1.item != i2.item GROUP BY i2.item ORDER BY i1.item DESC LIMIT 50;
	");

	echo "Client $i (".$client["id"].") of ".count($clients)."\n";

	foreach( $impressions as $impresson ) {
		$extractor->addString($impresson["title"], 3);
		$extractor->addString($impresson["text"], 1);
	}

	$buzzwords = $extractor->extract();

	$query = "INSERT INTO contest.clientbuzzword(client, buzzword, count) VALUES ";

	$i = 0;

	foreach( $buzzwords as $buzzword => $count ) {
		if( $count < 2 ) {
			continue;
		}
		$query .= ( $i == 0 ? "" : ",")."(".$client["id"].",'".mysql_real_escape_string($buzzword)."',".$count.")";
		$i++;

	}
	if( $i > 0 ) {
		$db->query($query);
	}

	$extractor->reset();
}

echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Similaritems finished\n", FILE_APPEND);

?>