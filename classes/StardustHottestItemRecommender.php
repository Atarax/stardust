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

			$db = new DatabaseManager();
			$data = $db->query("
					SELECT item.id AS item
					FROM contest.item, contest.hottestitemscore
					WHERE item.id = hottestitemscore.item AND
						  item.domain = ".$domainid." AND
						  item.recommendable > 0 AND
						  item.id > 0
					ORDER BY hottestitemscore.score DESC
					LIMIT 15;
			");
			$result_data = array();
			$i = 0;

			shuffle($data);
			// iterate over the data array
			foreach ($data as $row) {
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

			if ($i > $contestImpression->limit) {
				return $result_data;
			}
	}
}