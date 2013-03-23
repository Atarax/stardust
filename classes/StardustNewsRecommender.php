<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustNewsRecommender implements ContestRecommender {
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

		$db = DatabaseManager::getInstace();
		$data = $db->query("
				SELECT item.id AS item, item.title
				FROM contest.item, contest.newsscore
				WHERE item.id = newsscore.item AND
					  item.domain = ".$domainid." AND
					  item.recommendable > 0 AND
					  item.id > 0
					  ".$filter."
				ORDER BY newsscore.score DESC
				LIMIT 15;
		");

		shuffle($data);

		$result = array();

		$i = 0;
		// iterate over the data array
		foreach ($data as $row) {
			if(is_object($contestImpression->item) && $contestImpression->item->id > 0 && $row["id"] == $contestImpression->item->id) {
				continue;
			}

			$result[] = array("id" => $row["id"], "title" => $row["title"]);

			// don't return more items than asked for
			if (++$i > $contestImpression->limit) {
				break;
			}
		}

		return $result;
	}
}