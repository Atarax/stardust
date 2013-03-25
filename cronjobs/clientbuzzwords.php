<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Clientbuzzword started\n", FILE_APPEND);

// update migrations as well
$table = "CREATE TABLE `clientbuzzword_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `client` int(11) NOT NULL,
  `buzzword` varchar(80) NOT NULL,
  `count` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$clients = $db->query("
				SELECT DISTINCT client AS id FROM contest.impression WHERE client IS NOT NULL;
			");

$db->query("DROP TABLE IF EXISTS clientbuzzword_tmp");
$db->query($table);

$extractor = new BuzzwordExtractor();
$total = count($clients);

foreach( $clients as $i => $client ) {
	echo "Client $i (".$client["id"].") of ".$total."\n";

	$items = $db->query("SELECT item.title, item.text FROM contest.impression, contest.item WHERE item.id = impression.item AND client = ".$client["id"]." AND client IS NOT NULL");
	$items = array_merge(
			$items,
			$db->query("SELECT item.title, item.text FROM contest.feedback, contest.item WHERE item.id = feedback.target AND feedback.client = ".$client["id"]." AND client IS NOT NULL")
	);

	foreach( $items as $item ) {
		$extractor->addString($item["title"], 3);
		$extractor->addString($item["text"], 1);
	}

	$buzzwords = $extractor->extract();

	$query = "INSERT INTO contest.clientbuzzword_tmp(client, buzzword, count) VALUES ";

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

$db->query("CREATE INDEX clientANDbuzzword ON clientbuzzword_tmp (client,buzzword)");
$db->query("CREATE INDEX buzzword ON clientbuzzword_tmp (buzzword)");
$db->query("DROP TABLE IF EXISTS clientbuzzword");
$db->query("RENAME TABLE clientbuzzword_tmp TO clientbuzzword");
echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Itembuzzword finished\n", FILE_APPEND);

?>