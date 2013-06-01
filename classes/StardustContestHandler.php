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
		// TODO: Remove confusing characters to fix the duplicate recommendation bug
		$item = $contestImpression->item;

		if( is_object($item) ) {
			if( $item->title != null ) {
				$item->title = preg_replace('/[^\P{C}\n]+/u', '', $item->title);
			}
		}

		$client = $contestImpression->client;
		$domain = $contestImpression->domain;
		$context = isset($item) && isset($item->context) ? $item->context : null;
		$result_data = array();

		if ($contestImpression->recommend) {
			$fallback = true;
			if( is_object($item) && $item->id > 0 ) {
				$recommender = new StardustShanonRecommender();
				$result_data = $recommender->getRecommendations($contestImpression);

				if( count($result_data) >= $contestImpression->limit ) {
					$fallback = false;
				}
			}

			if( $fallback ) {
				$recommender = new StardustHottestItemRecommender();
				$result_data = $this->mergeRecommendations($result_data, $recommender->getRecommendations($contestImpression));
			}

			$answer = array();
			$i = 0;
			foreach($result_data as $result) {
				if (++$i > $contestImpression->limit) {
					break;
				}

				$data_object = new stdClass;
				$data_object->id = $result["id"];

				$answer[] = $data_object;
			}

			// post the result back to the contest server
			if( !DEBUG_ENVIRONMENT) {
				if(empty($answer)) {
					file_put_contents("log/emptyrecs", date('c') ." Recommendations empty!?\n".print_r($contestImpression,true)."\n".print_r($result_data,true)."\n", FILE_APPEND);
				}
				// construct a result message
				$result_object = new stdClass;
				$result_object->items = $answer;
				$result_object->team = $contestImpression->team;
				$result = ContestMessage::createMessage('result', $result_object);
				$result->postBack();
				fastcgi_finish_request();
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
		$myItem->title = isset($item) ? preg_replace('/[^\P{C}\n]+/u', '', $item->title) : null;
		$myItem->img = isset($item) && isset($item->img) ? $item->img : null;

		if( isset($myItem->id) && $myItem->id > 0) {
			$myItem->save();
		}

		if( isset($recommender) ) {
			if($recommender instanceof StardustHottestItemRecommender) {
				$recommenderid = 1;
			}
			else if ($recommender instanceof StardustNewsRecommender) {
				$recommenderid = 2;
			}
			else if($recommender instanceof StardustSimilarRecommender) {
				$recommenderid = 3;
			}
			else if($recommender instanceof StardustSimilarRecommenderInstant) {
				$recommenderid = 4;
			}
			else {
				$recommenderid = 5;
			}

			foreach($result_data as $record) {
				$recommendation = new Recommendation();
				$recommendation->source = $myItem->id;
				$recommendation->item = $record["id"];
				$recommendation->client = $impression->client;
				$recommendation->recommender = $recommenderid;
				$recommendation->save();
			}
		}
	}

	private function mergeRecommendations($data1, $data2) {
		foreach($data2 as $d2) {
			if( !$this->contains($data1,$d2) ) {
				$data1[] = $d2;
			}
		}

		return $data1;
	}

	private function contains($data, $item) {
		foreach($data as $d) {
			if($d["id"] == $item["id"] || $d["title"] == $item["title"]) {
				return true;
			}
		}
		return false;
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