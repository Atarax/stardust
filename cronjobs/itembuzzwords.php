<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Itembuzzword started\n", FILE_APPEND);

// update migrations as well
$table = "CREATE TABLE `itembuzzword_tmp` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item` int(11) NOT NULL,
  `buzzword` varchar(80) NOT NULL,
  `count` float DEFAULT '0',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8";

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$items = $db->query("
				SELECT id FROM contest.item;
			");

$db->query("DROP TABLE IF EXISTS itembuzzword_tmp");
$db->query($table);

//$db->query("TRUNCATE TABLE contest.itembuzzword");
$extractor = new BuzzwordExtractor();

foreach( $items as $i => $item ) {
	echo "Item $i (".$item["id"].") of ".count($items)."\n";

	$extractor->addString($item["title"], 3);
	$extractor->addString($item["text"], 1);

	$buzzwords = $extractor->extract();

	$query = "INSERT INTO contest.itembuzzword_tmp(item, buzzword, count) VALUES ";

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

$db->query("CREATE INDEX itemANDbuzzword ON itembuzzword_tmp (item,buzzword)");
$db->query("CREATE INDEX buzzword ON itembuzzword_tmp (buzzword)");
$db->query("DROP TABLE IF EXISTS itembuzzword");
$db->query("RENAME TABLE itembuzzword_tmp TO itembuzzword");
echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Itembuzzword finished\n", FILE_APPEND);

?>