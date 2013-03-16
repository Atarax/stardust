<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Hottest-Item started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$db = new DatabaseManager();
$db->connect();
$data = $db->query("
				SELECT COUNT(impression.id) AS score, impression.item
				FROM contest.impression, contest.item
				WHERE item.id = impression.item AND
					item.recommendable > 0 AND
					impression.item != 0 AND
					DATEDIFF(NOW(), impression.created) <= 1
				GROUP BY item
				ORDER BY score DESC
			");

$db->query("TRUNCATE TABLE contest.hottestitemscore");

foreach( $data as $row ) {
	if( !$row["item"] ) {
		continue;
	}
	$scoreModel = new HottestItemScore();
	$scoreModel->item = $row["item"];
	$scoreModel->score = $row["score"];
	$scoreModel->save();
}

echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Hottest-Item finished\n", FILE_APPEND);

?>