<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustShanonRecommender implements ContestRecommender {

	public function getRecommendations(ContestImpression $contestImpression) {
		$domainid = $contestImpression->domain->id;
		$clientid = is_object($contestImpression->client) && $contestImpression->client->id > 0 ? $contestImpression->client->id : 0;

		if( $clientid > 0 ) {
			$filter = " AND item.id NOT IN (SELECT item FROM contest.recommendation WHERE client IS NOT NULL and client = ".$clientid.") ";
			$filter .= " AND item.id NOT IN (SELECT item FROM contest.impression WHERE client IS NOT NULL and client = ".$clientid.") ";
		}
		else {
			$filter = "";
		}

		$extractor = new BuzzwordExtractor();
		// TODO: Add title and compare results, lets see if its even more cool ^^
		$extractor->addString($contestImpression->item->title);

		$buzzwords = $extractor->extract(true);
		//file_put_contents("log/release2", date('c') . " Data (".print_r(explode(",",array_keys($buzzwords)), true)."\n", FILE_APPEND);

		$db = DatabaseManager::getInstace();
		$db->connect();

		$tmp = array();
		foreach(array_keys($buzzwords) as $word) {
			$tmp[] = "'".mysql_real_escape_string($word)."'";
		}

		$query = "SELECT id AS item, title FROM (
			SELECT
				item.id,
				item.title,
				SUM(itembuzzword.count * buzzword.information) AS score
			FROM
				itembuzzword,
				item,
				buzzword
			WHERE
				buzzword.buzzword = itembuzzword.buzzword AND
				item.id = itembuzzword.item AND
				itembuzzword.buzzword IN (".implode(",", $tmp ).") AND
				item.id != ".$contestImpression->item->id." AND
				item.domain = ".$domainid." AND
				item.recommendable > 0 AND
				item.title NOT LIKE '".mysql_real_escape_string($contestImpression->item->title)."'
				".$filter."
			GROUP BY
				item.id
			) r1
			GROUP BY
				title
			ORDER BY
				score DESC
		";

		$data = $db->query($query);

		$result = array();

		$i = 0;
		// iterate over the data array
		foreach ($data as $row) {
			if(is_object($contestImpression->item) && $contestImpression->item->id > 0 && $row["item"] == $contestImpression->item->id) {
				continue;
			}

			$result[] = array("id" => $row["item"], "title" => $row["title"]);

			// don't return more items than asked for
			if (++$i > $contestImpression->limit) {
				break;
			}
		}

		return $result;
	}
}