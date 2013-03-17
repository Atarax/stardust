<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Clientbuzzword started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$clients = $db->query("
				SELECT DISTINCT client AS id FROM contest.impression WHERE client IS NOT NULL;
			");

$db->query("TRUNCATE TABLE contest.clientbuzzword");
$extractor = new BuzzwordExtractor();

foreach( $clients as $i => $client ) {
	$impressions = $db->query("
		SELECT item.title, item.text
		FROM contest.item, contest.impression
		WHERE client = ".$client["id"]." AND item.id = impression.item AND impression.item != 0
	");

	echo "Client $i (".$client["id"].") of ".count($clients)."\n";

	foreach( $impressions as $impresson ) {
		$extractor->addString($impresson["title"], 3);
		$extractor->addString($impresson["text"], 1);
	}

	$buzzwords = $extractor->extract();

	$query = "INSERT INTO contest.clientbuzzword(client, buzzword, count) VALUES ";

	$i = 0;
	$size = count($buzzwords);
	foreach( $buzzwords as $buzzword => $count ) {
		if( $count < 2 ) {
			continue;
		}
		$query .= ( $i == 0 ? "" : ",")."(".$client["id"].",'".mysql_real_escape_string($buzzword)."',".$count.")";
		$i++;
		/*
		$model = new ClientBuzzword();
		$model->client = $client["id"];
		$model->count = $count;
		$model->buzzword = $buzzword;
		$model->save();
		*/
	}
	if( $size > 0 ) {
		file_put_contents(LOG_PATH."trash", date('c') . print_r($query, true)."\n", FILE_APPEND);
		$db->query($query);
	}

	$extractor->reset();
}

echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Clientbuzzword finished\n", FILE_APPEND);

?>