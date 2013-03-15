<?php
require_once("../config.php");

file_put_contents("hottestitem.log", date('c') . " Newsc-Scan started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$db = new DatabaseManager();
$db->connect();
$data = $db->query("
				SELECT COUNT(impression.id) AS score, impression.item
				FROM contest.impression, contest.item
				WHERE item.id = impression.item AND
					item.recommendable > 0 AND
					impression.item != 0
				GROUP BY item
				ORDER BY score DESC
			");

$db->query("TRUNCATE TABLE contest.hottestitemscore");

foreach( $data as $row ) {

	$score = $score/count($itemwords);
	$scoreModel = new NewsScore();
	$scoreModel->item = $row["id"];
	$scoreModel->score = $row["score"];
	$scoreModel->save();
}

echo "Finished.".PHP_EOL;

$db->close();
file_put_contents("hottestitem.log", date('c') . " Newsc-Scan finished\n", FILE_APPEND);

?>