<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustSimilarRecommender implements ContestRecommender {

	public function getRecommendations(ContestImpression $contestImpression) {
		$domainid = $contestImpression->domain->id;
		$clientid = is_object($contestImpression->client) && $contestImpression->client->id > 0 ? $contestImpression->client->id : 0;

		if( $clientid > 0 ) {
			$filter = " AND similaritems.similaritem NOT IN (SELECT item FROM contest.recommendation WHERE client IS NOT NULL and client = ".$clientid.") ";
			$filter .= " AND similaritems.similaritem NOT IN (SELECT item FROM contest.impression WHERE client IS NOT NULL and client = ".$clientid.") ";
		}
		else {
			$filter = "";
		}

		$db = DatabaseManager::getInstace();
		$db->connect();

		$query = "
			SELECT similaritems.similaritem AS itemid,
					item.title
					FROM contest.similaritems, contest.item
					WHERE item.id = similaritems.similaritem AND
						item.recommendable > 0 AND
						item.title != '".mysql_real_escape_string($contestImpression->item->title)."' AND
						similaritems.item = ".$contestImpression->item->id." AND
						similaritems.similaritem != ".$contestImpression->item->id.$filter."
					GROUP BY title
					ORDER BY similarity DESC
			";

		$data = $db->query($query);

		$result = array();

		$i = 0;
		// iterate over the data array
		foreach ($data as $row) {
			if(is_object($contestImpression->item) && $contestImpression->item->id > 0 && $row["itemid"] == $contestImpression->item->id) {
				continue;
			}
			$result[] = array("id" => $row["itemid"], "title" => $row["title"]);

			// don't return more items than asked for
			if (++$i > $contestImpression->limit) {
				break;
			}
		}

		return $result;
	}
}