<?php
/* This is the entry point for the example implementation of the Plista Prize API. If you decide to use it
 * as basis for your implementation, configure your server such that this file is accessible over the internet.
 * Also remember to update your profile on the Plista Prize website with the complete URL of this file.
 */

// load some common functions and constants
require_once 'config.php';

//$t1 = microtime(true);

// $handler variable is an implementation of the interface ContestHandler. put your application logic there.
$handler = StardustContestHandler::getInstance();

// read entire message body into a variable
$msg = file_get_contents("php://input");

// the message may arrive url encoded
$msg = urldecode($msg);

$mirror = new MessageMirror();


try {

	// parse plain json into a ContestMessage
	$msg = ContestMessage::fromJSON($msg);

	if (!$msg) {
		throw new ContestException('parsing json failed', 400);
	}

	// log the message

	if ($msg instanceof ContestImpression) {
		// call the handler method, which is also responsible for posting the data back to the contest server
		$handler->handleImpression($msg);
	} else if ($msg instanceof ContestFeedback) {
		// no response required here
		$handler->handleFeedback($msg);
	} else if ($msg instanceof ContestError) {
		// yup, it's an error
		file_put_contents(LOG_PATH."error", date('c') . "Error: ".print_r($msg, true)."\n--------------------------------------------------\n\n", FILE_APPEND);
		$handler->handleError($msg);
	} else {
		// we don't know how to handle anything else
		file_put_contents($config["logfile"], date('c') . " Error: ".print_r($e, true)."\n", FILE_APPEND);
		throw new ContestException('unknown message type: ' . get_class($msg));
	}

	try {
		$mirror->mirror($msg);
	}
	catch( Exception $e ) {

	}

} catch (ContestException $e) {
	// we forward every error we catch back to the server

	$e->getError()->postBack();

	// and also log it
	file_put_contents($config["logfile"], date('c') . " Error: ".print_r($e, true)."\n", FILE_APPEND);
}


//$duration = microtime(true) - $t1;
//if( $duration > 0.1 ) {
	//file_put_contents("log/timeout", date('c') . " Probable Timeout: ".sprintf('%.3f', $duration)."\n", FILE_APPEND);
//}
//file_put_contents("log/executiontime", date('c') . " Execution Time: ".sprintf('%.3f', $duration)."\n", FILE_APPEND);
