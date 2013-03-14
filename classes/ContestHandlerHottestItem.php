<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class ContestHandlerHottestItem implements ContestHandler {
	// holds the instance, singleton pattern
	private static $instance;

	private function __construct() { }

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new ContestHandlerHottestItem();
		}

		return self::$instance;
	}

	/* This method handles received impressions. First it loads the data file, then checks whether the current item is
	 * present in the data. If not, it prepends the new item id and writes the data file back. It then checks whether
	 * it needs to generate a recommendation and if so takes object ids from the front of the data (excluding the new one)
	 * and sends those back to the contest server.
	 */
	public function handleImpression(ContestImpression $contestImpression) {
		// check whether a recommendation is expected. if the flag is set to false, the current message is just a training message.
		if ($contestImpression->recommend) {
			$db = new DatabaseManager();
			$data = $db->query("SELECT COUNT(impression.id) AS visits, impression.item FROM contest.impression, contest.item WHERE item.id = impression.item AND item.recommendable > 0 AND impression.item IS NOT NULL GROUP BY item ORDER BY visits DESC LIMIT 20");

			$result_data = array();
			$i = 0;

			// iterate over the data array
			foreach ($data as $row) {
				// don't return more items than asked for
				if (++$i > $contestImpression->limit) {
					break;
				}

				$data_object = new stdClass;
				$data_object->id = $row["item"];

				$result_data[] = $data_object;
			}

			if ($i <= $contestImpression->limit) {
				throw new ContestException('not enough data', 500);
			}

			// construct a result message
			$result_object = new stdClass;
			$result_object->items = $result_data;
			$result_object->team = $contestImpression->team;

			$result = ContestMessage::createMessage('result', $result_object);
			// post the result back to the contest server
			$result->postBack();

		}

		// Accumulate Data
		$item = $contestImpression->item;
		$client = $contestImpression->client;
		$domain = $contestImpression->domain;
		$context = isset($item) && isset($item->context) ? $item->context : null;

		$impression = new Impression();
		$impression->id = isset($contestImpression->id) ? $contestImpression->id : 0;
		$impression->client = isset($client) ? $client->id : null;
		$impression->domain = isset($domain) ? $domain->id : null;
		$impression->item = isset($item) ? $item->id : null;
		$impression->save();

		$myItem = new Item();
		$myItem->id = isset($item->id) ? $item->id : 0;
		$myItem->recommendable = isset($item) ? $item->recommendable : true;
		$myItem->domain = isset($domain) ? $domain->id : null;
		$myItem->category = isset($context) ? $context->category : null;
		$myItem->text = isset($item) ? $item->text : null;
		$myItem->url = isset($item) ? $item->url : null;
		$myItem->created = isset($item) && isset($item->created) ? date("y-m-d h:i:s", $item->created) : null;
		$myItem->title = isset($item) ? $item->title : null;
		$myItem->img = isset($item) && isset($item->img) ? $item->img : null;
		$myItem->save();

		if( isset($result_data) ) {
			foreach($result_data as $record) {
				$recommendation = new Recommendation();
				$recommendation->source = $myItem->id;
				$recommendation->item = $record->id;
				$recommendation->recommender = 1;
				$recommendation->save();
			}
		}
	}

	/* This method handles feedback messages from the contest server. As of now it does nothing. It could be used to look at
	 * the object ids in the feedback message and possibly add those to the data list as well.
	 */
	public function handleFeedback(ContestFeedback $contestFeedback) {
		/*
		file_put_contents("log/queries", date('c') .print_r($contestFeedback, true)."\n", FILE_APPEND);
		file_put_contents("log/queries", date('c') .print_r($contestFeedback->source, true)."\n", FILE_APPEND);
		*/
		$feedback = new Feedback();

		$save = false;
		if (!empty($contestFeedback->source)) {
			$feedback->source = $contestFeedback->source->id;
			$save = true;
		}

		if (!empty($contestFeedback->target)) {
			$feedback->target = $contestFeedback->target->id;
			$save = true;
		}

		if (!empty($contestFeedback->client)) {
			$feedback->client = $contestFeedback->client->id;
			$save = true;
		}

		if (!empty($contestFeedback->domain)) {
			$feedback->domain = $contestFeedback->domain->id;
			$save = true;
		}

		if($save) {
			$feedback->save();
		}
	}

	/* This is the handler method for error messages from the contest server. Implement your error handling code here.
	 */
	public function handleError(ContestError $error) {
		//echo 'oh no, an error: ' . $error->getMessage();
		throw new ContestException($error);
	}
}