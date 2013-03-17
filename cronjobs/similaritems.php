<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Similaritems started\n", FILE_APPEND);

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$items = $db->query("
				SELECT id FROM contest.item;
			");

$db->query("TRUNCATE TABLE contest.similaritems");

foreach( $items as $i => $item ) {
	$relateditems = $db->query("
		SELECT i2.item AS id, i1.count*i2.count AS similarity FROM contest.itembuzzword i1, contest.itembuzzword i2 WHERE i1.item = ".$item["id"]." AND i1.buzzword = i2.buzzword AND i1.item != i2.item ORDER BY similarity DESC LIMIT 20;
	");

	echo "Item $i (".$item["id"].") of ".count($items)."\n";

	$query = "INSERT INTO contest.similaritems(item, similaritem, similarity) VALUES ";

	foreach( $relateditems as $i => $relateditem) {
		$query .= ( $i == 0 ? "" : ",")."(".$item["id"].",".$relateditem["id"].",".$relateditem["similarity"].")";
	}

	if( count($relateditems) > 0 ) {
		$db->query($query);
	}
}

echo "Finished.".PHP_EOL;

file_put_contents(LOG_PATH."cronjobs", date('c') . " Similaritems finished\n", FILE_APPEND);

?>