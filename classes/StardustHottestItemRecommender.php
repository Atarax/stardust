<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustHottestItemRecommender implements ContestRecommender {

	public function getRecommendations(ContestImpression $contestImpression) {
			$domainid = $contestImpression->domain->id;
			$clientid = is_object($contestImpression->client) && $contestImpression->client->id > 0 ? $contestImpression->client->id : 0;

			if( $clientid > 0 ) {
				$filter = " AND item.id NOT IN (SELECT item FROM contest.recommendation WHERE client IS NOT NULL and client = ".$clientid.") ";
				$filter .= " AND item.id NOT IN (SELECT item FROM contest.impression WHERE client IS NOT NULL and client = ".$clientid." AND item IS NOT NULL) ";
			}
			else {
				$filter = "";
			}

			$db = DatabaseManager::getInstace();
			$query = "
					SELECT item.id AS item, item.title
					FROM contest.item, contest.hottestitemscore
					WHERE item.id = hottestitemscore.item AND
						  item.domain = ".$domainid." AND
						  item.recommendable > 0 AND
						  item.id > 0
						  ".$filter."
					ORDER BY hottestitemscore.score DESC
					LIMIT 15
			";

			$t1 = microtime(true);
			$data = $db->query($query);
			$duration = microtime(true) - $t1;

			file_put_contents("log/hottestexecutiontime", date('c') . " Execution Time: ".sprintf('%.3f', $duration)."\n", FILE_APPEND);

			shuffle($data);

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