<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 7:11 PM
 * To change this template use File | Settings | File Templates.
 */
class StardustContestHandler implements ContestHandler{
	// holds the instance, singleton pattern
	private static $instance;

	private function __construct() { }

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new StardustContestHandler();
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
		$item = $contestImpression->item;
		$client = $contestImpression->client;
		$domain = $contestImpression->domain;
		$context = isset($item) && isset($item->context) ? $item->context : null;

		if ($contestImpression->recommend) {
			$rand = rand(0,1000);
			if( $rand <= 500 ) {
				$recommender = new StardustNewsRecommender();
			}
			else {
				$recommender = new StardustHottestItemRecommender();
			}

			$result_data = 	$recommender->getRecommendations($contestImpression);

			// post the result back to the contest server
			if( !DEBUG_ENVIRONMENT) {
				// construct a result message
				$result_object = new stdClass;
				$result_object->items = $result_data;
				$result_object->team = $contestImpression->team;

				$result = ContestMessage::createMessage('result', $result_object);
				$result->postBack();
			}
		}

		// Accumulate Data
		$impression = new Impression();
		$impression->id = isset($contestImpression->id) ? $contestImpression->id : 0;
		$impression->client = isset($client) ? $client->id : null;
		$impression->domain = isset($domain) ? $domain->id : null;
		$impression->recommend = $contestImpression->recommend;
		$impression->item = isset($item) ? $item->id : null;
		$impression->save();

		$myItem = new Item();
		$myItem->id = isset($item->id) ? $item->id : 0;
		$myItem->recommendable = isset($item) ? $item->recommendable : false;
		$myItem->domain = isset($domain) ? $domain->id : null;
		$myItem->category = isset($context) ? $context->category : null;
		$myItem->text = isset($item) ? $item->text : null;
		$myItem->url = isset($item) ? $item->url : null;
		$myItem->created = isset($item) && isset($item->created) ? date("y-m-d h:i:s", $item->created) : null;
		$myItem->title = isset($item) ? $item->title : null;
		$myItem->img = isset($item) && isset($item->img) ? $item->img : null;

		if( isset($myItem->id) && $myItem->id > 0) {
			$myItem->save();
		}

		if( isset($result_data) ) {
			foreach($result_data as $record) {
				$recommendation = new Recommendation();
				$recommendation->source = $myItem->id;
				$recommendation->item = $record->id;
				$recommendation->client = $impression->client;
				$recommendation->recommender = 2;
				$recommendation->save();
			}
		}
	}

	/* This method handles feedback messages from the contest server. As of now it does nothing. It could be used to look at
	 * the object ids in the feedback message and possibly add those to the data list as well.
	 */
	public function handleFeedback(ContestFeedback $contestFeedback) {

		/*
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

		if (!empty($contestFeedback->team)) {
			$feedback->team = $contestFeedback->team->id;
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