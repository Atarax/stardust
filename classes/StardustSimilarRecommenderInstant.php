<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustSimilarRecommenderInstant implements ContestRecommender {

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
		$extractor->addString($contestImpression->item->title);

		$buzzwords = $extractor->extract();
		//file_put_contents("log/release2", date('c') . " Data (".print_r(explode(",",array_keys($buzzwords)), true)."\n", FILE_APPEND);

		$db = DatabaseManager::getInstace();
		$db->connect();

		$tmp = array();
		foreach(array_keys($buzzwords) as $word) {
			$tmp[] = "'".mysql_real_escape_string($word)."'";
		}

		$query = "
			SELECT * FROM (
			SELECT
				ib2.item,
				SUM(ib2.count) AS similarity,
				item.title
			FROM
				contest.item,
				contest.itembuzzword ib1,
				contest.itembuzzword ib2
			WHERE
				ib2.buzzword IN (".mysql_real_escape_string( implode(",", $tmp ) ).") AND
				item.id = ib2.item AND
				item.recommendable > 0 AND
				item.title != '".mysql_real_escape_string($contestImpression->item->title)."' AND
				item.domain = ".$domainid.$filter."
			GROUP BY
				ib2.item
			) r1
			GROUP BY title
			ORDER BY
				r1.similarity DESC
			";

		file_put_contents("log/release", date('c') . " Data (".$query."\n", FILE_APPEND);
		$data = $db->query($query);

		$result_data = array();
		$i = 0;
		// iterate over the data array
		foreach ($data as $row) {
			//file_put_contents("log/similar", date('c') . " Data (".$contestImpression->item->id.": ".print_r($row, true)."\n", FILE_APPEND);
			if(is_object($contestImpression->item) && $contestImpression->item->id > 0 && $row["item"] == $contestImpression->item->id) {
				continue;
			}
			// don't return more items than asked for
			if (++$i > $contestImpression->limit) {
				break;
			}

			$data_object = new stdClass;
			$data_object->id = $row["item"];
			$result_data[] = $data_object;
		}
		//if ($i > $contestImpression->limit) {
		return $result_data;
		//}

	}
}