<?php
/* This is the reference implementation of a ContestHandler, which does nothing more than store the last items it sees
 * (through impressions), and return them in reverse order.
 */

class ContestHandlerLIFO implements ContestHandler {
	// holds the instance, singleton pattern
    private static $instance;

	private function __construct() { }

	public static function getInstance() {
		if (self::$instance == null) {
			self::$instance = new ContestHandlerLIFO();
		}

		return self::$instance;
	}

	/* This method handles received impressions. First it loads the data file, then checks whether the current item is
	 * present in the data. If not, it prepends the new item id and writes the data file back. It then checks whether
	 * it needs to generate a recommendation and if so takes object ids from the front of the data (excluding the new one)
	 * and sends those back to the contest server.
	 */
	public function handleImpression(ContestImpression $contestImpression) {
		$domainid = $contestImpression->domain->id;
		$filename = "data_contest_$domainid.txt";

		if (!file_exists($filename)) {
			// try to create file
			if (!@file_put_contents($filename, '0')) {
				throw new ContestException('could not create data file', 500);
			}
		}

		// read data file
		$data = file_get_contents($filename);

		if (strlen($data) < 1) {
			throw new ContestException('could not read data file', 500);
		}

		// parse into proper format, ie an array
		if (strpos($data, ',') !== false) {
			$data = explode(',', $data);
		} else if (strlen($data) > 1) {
			$data = array($data);
		} else {
			$data = array(0);
		}
		file_put_contents("log/queries", date('c') . " begin\n", FILE_APPEND);

		$impression = new Impression();
		$impression->id = isset($contestImpression->id) ? $contestImpression->id : 0;
		$impression->client = $contestImpression->client->id;
		$impression->domain = $contestImpression->domain->id;
		$impression->item = $contestImpression->item->id;
		$impression->save();

		$item = new Item();
		$item->id = isset($contestImpression->item->id) ? $contestImpression->item->id : 0;
		$item->recommendable = isset($contestImpression->item->recommendable) ? $contestImpression->item->recommendable : true;
		$item->domain = $domainid;
		$item->category = $contestImpression->item->context->category;
		$item->text = $contestImpression->item->text;
		$item->url = $contestImpression->item->url;
		$item->created = isset($contestImpression->item->created) ? date("y-m-d h:i:s", $contestImpression->item->created) : null;
		$item->title = $contestImpression->item->title;
		$item->img = $contestImpression->item->img;
		$item->save();


		// check to see whether the current item id is contained in the data set
		if ($item->id > 0 && !in_array($item->id, $data) && $item->recommendable) {
			// prepend it to the data, if not
			if (count($data) > 10) {
				array_pop($data);
			}

			array_unshift($data, $item->id);

			$data_string = implode(',', $data);
			// and write the file back
			file_put_contents($filename, $data_string);
		}

		// check whether a recommendation is expected. if the flag is set to false, the current message is just a training message.
		if ($contestImpression->recommend) {
			$result_data = array();
			$i = 0;

			// iterate over the data array
			foreach ($data as $data_item) {
				// exclude the new item id
				if ($data_item == $item->id) {
					continue;
				}

				// don't return more items than asked for
				if (++$i > $contestImpression->limit) {
					break;
				}

				$data_object = new stdClass;
				$data_object->id = $data_item;

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

	}

	/* This method handles feedback messages from the contest server. As of now it does nothing. It could be used to look at
	 * the object ids in the feedback message and possibly add those to the data list as well.
	 */
	public function handleFeedback(ContestFeedback $contestFeedback) {
		$feedback = new Feedback();
		$feedback->client = $contestFeedback->client->id;
		$feedback->source = $contestFeedback->source->id;
		$feedback->target = $contestFeedback->target->id;

		$save = false;
		if (!empty($contestFeedback->source)) {
			$itemid = $contestFeedback->source->id;
			$save = true;
		}

		if (!empty($contestFeedback->target)) {
			$itemid = $contestFeedback->target->id;
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