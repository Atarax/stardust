<?php
require_once("../config.php");

file_put_contents(LOG_PATH."cronjobs", date('c') . " Buzzword started\n", FILE_APPEND);

// update migrations as well
$table = " CREATE TABLE buzzword_tmp (
  id int(11) NOT NULL AUTO_INCREMENT,
  buzzword varchar(80) NOT NULL,
  count int(11) DEFAULT NULL,
  information float DEFAULT NULL,
  PRIMARY KEY (id)
) DEFAULT CHARSET=utf8
";

mb_internal_encoding('UTF-8');

$db = DatabaseManager::getInstace();
$db->connect();
$items = $db->query("
				SELECT DISTINCT id, title, text FROM contest.item;
			");

$db->query("DROP TABLE IF EXISTS buzzword_tmp");
$db->query($table);

$extractor = new BuzzwordExtractor();

foreach( $items as $i => $item ) {
$extractor->addString($item["title"], 3);
	$extractor->addString($item["text"], 1);
}

$buzzwords = $extractor->extract(true);

$query = "INSERT INTO contest.buzzword_tmp(buzzword, count, information) VALUES ";

$i = 0;
$total = count($buzzwords);

foreach( $buzzwords as $buzzword => $count ) {
	if(strlen($buzzword) <= 1) {
		continue;
	}
	echo "Buzzword $i (".$buzzword.") of ".$total."\n";

	$query .= ( $i == 0 ? "" : ",")."('".mysql_real_escape_string($buzzword)."',".$count.",".( -log( $count/$total, 2 ) ).")";
	$i++;
}
if( $i > 0 ) {
	$db->query($query);
}

$db->query("CREATE INDEX buzzword ON buzzword_tmp (buzzword)");
$db->query("DROP TABLE IF EXISTS buzzword");
$db->query("RENAME TABLE buzzword_tmp TO buzzword");
echo "Finished.".PHP_EOL;



file_put_contents(LOG_PATH."cronjobs", date('c') . " Buzzword finished\n", FILE_APPEND);

?>