<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Hottest-Item started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$table = "
 CREATE TABLE `hottestitemscore_tmp` (
  `item` int(11) NOT NULL,
  `score` float DEFAULT 0,
  PRIMARY KEY (`item`)
) DEFAULT CHARSET=utf8";

$db = DatabaseManager::getInstace();
$db->connect();

$db->query("DROP TABLE IF EXISTS hottestitemscore_tmp");
$db->query($table);

$data = $db->query("
	SELECT DISTINCT item.id AS item
	FROM contest.item
	WHERE item.recommendable > 0
");

//$db->query("TRUNCATE TABLE contest.hottestitemscore");

$total = count($data);

foreach( $data as $i => $row ) {
	if( !$row["item"] ) {
		continue;
	}
	echo "Item ".$i." of ".$total.PHP_EOL;
	$score = $db->query("SELECT COUNT(*) AS score FROM contest.impression WHERE impression.item = ".$row["item"]." AND DATEDIFF(NOW(),created) <= 1");
	$query = "INSERT INTO contest.hottestitemscore_tmp(item, score) VALUES (".$row["item"].",".$score[0]["score"].")";

	$db->query($query);
}

$db->query("CREATE INDEX itemANDscore ON hottestitemscore_tmp (item,score)");
$db->query("CREATE INDEX score ON hottestitemscore_tmp (score)");
$db->query("DROP TABLE IF EXISTS hottestitemscore");
$db->query("RENAME TABLE hottestitemscore_tmp TO hottestitemscore");

echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Hottest-Item finished\n", FILE_APPEND);

?>