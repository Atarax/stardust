<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustNewsRecommender implements ContestHandler{
	public function recommend(ContestImpression $contestImpression) {
		$domainid = $contestImpression->domain->id;

		$db = new DatabaseManager();
		$data = $db->query("
				SELECT item.id AS item
				FROM contest.item, contest.newsscore
				WHERE item.id = newsscore.item AND
					  item.domain = ".$domainid." AND
					  item.recommendable > 0 AND
					  item.id > 0
				ORDER BY newsscore.score DESC
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

		file_put_contents("log/response", date('c') .print_r($result_data, true)."\n", FILE_APPEND);

		if ($i > $contestImpression->limit) {
			// construct a result message
			$result_object = new stdClass;
			$result_object->items = $result_data;
			$result_object->team = $contestImpression->team;

			$result = ContestMessage::createMessage('result', $result_object);
			// post the result back to the contest server
			if( !DEBUG_ENVIRONMENT) {
				$result->postBack();
			}
		}
	}
}