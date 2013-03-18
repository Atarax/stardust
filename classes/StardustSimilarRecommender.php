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
		$data = $db->query("
					SELECT similaritems.item AS itemid
					FROM contest.similaritems, contest.item
					WHERE item.id = similaritems.similaritem AND item = ".$contestImpression->item->id.$filter."
					ORDER BY similarity DESC
			");
		$result_data = array();
		$i = 0;

		// iterate over the data array
		foreach ($data as $row) {
			if(is_object($contestImpression->item) && $contestImpression->item->id > 0 && $row["itemid"] == $contestImpression->item->id) {
				continue;
			}
			// don't return more items than asked for
			if (++$i > $contestImpression->limit) {
				break;
			}

			$data_object = new stdClass;
			$data_object->id = $row["itemid"];
			$result_data[] = $data_object;
			file_put_contents("log/similar", date('c') . " Item (".$$contestImpression->item->id.": ".print_r($data_object, true)."\n", FILE_APPEND);
		}
		//if ($i > $contestImpression->limit) {
			return $result_data;
		//}

	}
}