<?php
class ContestMessage {
	public $timestamp; // autogenerated upon instantiation
	public $team;
	
	const VERSION = '1.0';

	public function __construct($data = null) {
		if ($data != null && !is_object($data)) {
			throw new ContestException('object expected', 500);
		}

		if (isset($data->version) && $data->version != self::VERSION) {
			throw new ContestException('version mismatch', 400);
		}

		$this->timestamp = microtime(true);
	}

	public function __toString() {
		return print_r($this->__toArray(), true);
	}

	public function __toArray() {
		return array(
			'timestamp' => intval($this->timestamp),
			'team' => (isset($this->team) ? $this->team->id : null),
			'type' => strtolower(substr(get_class($this), 7)),
			'version' => self::VERSION,
		);
	}

	public function __toJSON() {
		return plista_json_encode($this->__toArray());
	}

	/**
	 * returns a mapping of the msg-property in the received json to the appropriate class this handler supports.
	 * @param $msg string the name of the message
	 * @return ContestMessage an instance of ContestMessage representing the requested message
	 * @throws ContestException if no appropriate ContestMessage type is known
	 */
	public static function createMessage($msg, $data = null) {
		switch ($msg) {
			case 'impression':
			case 'feedback':
			case 'result':
				$classname = 'Contest' . ucfirst($msg);
				return new $classname($data);
			case 'processing':
				return new ContestResponseImpression($data);
			case 'thanks':
				return new ContestResponseFeedback($data);
			default:
				throw new ContestException("unknown message type", 400);
		}
	}

	public static function createError($data) {
		if (!is_object($data)) {
			$obj = new stdClass;
			$obj->error = $data;
			$data = $obj;
		}

		return new ContestError($data);
	}

	/**
	 * static function to create a new instance of a ContestMessage directly from JSON input
	 *
	 * @param string $json
	 * @return ContestMessage
	 */
	public static function fromJSON($json) {
		if (empty($json)) {
			throw new ContestException("message may not be empty", 400);
		}

		// decode JSON
		$json_obj = json_decode($json);

		if (!$json_obj) {
			throw new ContestException("parsing json failed", 400);
		}

		if (!is_array($json_obj)) {
			$json_obj = array($json_obj);
		}

		$msg_arr = array();

		// the individual json objects need to be converted into actual contestmessages
		foreach ($json_obj as $msg) {
			// look at type and return appropriate class
			if (isset($msg->msg)) {
				//$msg_arr[] = static::createMessage($msg->msg, $msg);
				$msg_arr[] = self::createMessage($msg->msg, $msg);
			} else if (isset($msg->error)) {
				//$msg_arr[] = static::createError($msg);
				$msg_arr[] = self::createError($msg);
			} else {
				throw new ContestException("message syntax error", 400);
			}
		}

		return (count($msg_arr) == 1 ? $msg_arr[0] : $msg_arr);
	}

	/**
	 * Returns an appropriate response object for this message. Quasi abstract.
	 * @return ContestMessage $message
	 */
	public function getResponse() {
		return null;
	}

	public function setTeam($team = null) {
		if (isset($team->id) && $team->id < 1) {
			$team = null;
		}
		
		$this->team = $team;
	}

	public function getTeam() {
		return $this->team;
	}
	
	public function postTo($target, $fetch_response = true, $callback = null) {
		// create new HttpRequest
		$request = new HttpRequest($target, $callback);

		// post this message and return the result (which may be null)
		return $request->post($this, $fetch_response);
	}

	public function postBack() {
		if (!headers_sent()) {
			header('Content-Type: application/json');
		}

		echo plista_json_encode($this) . PHP_EOL;
		flush();
		ob_end_flush();

	}
	
	public function __get($name) {
		if (!isset($this->$name)) {
			return null;
		}

		return $this->$name;
	}

	public function __isset($name) {
		return isset($this->$name);
	}
}
