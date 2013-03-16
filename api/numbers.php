<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 6:41 PM
 * To change this template use File | Settings | File Templates.
 */
require_once("../config.php");

$db = new DatabaseManager();
$db->connect();

$data = $db->query("SELECT COUNT(id) AS count FROM contest.impression WHERE DATEDIFF(NOW(), created) = 0");
$impressioncount = isset($data[0]["count"]) ? $data[0]["count"] : 0;

$data = $db->query("SELECT COUNT(id) AS count FROM contest.item");
$itemcount = isset($data[0]["count"]) ?$data[0]["count"] : 0;

$data = $db->query("SELECT COUNT(id) AS count FROM contest.recommendation WHERE DATEDIFF(NOW(), created) = 0");
$recommendationcount = isset($data[0]["count"]) ?$data[0]["count"] : 0;

$data = $db->query("SELECT COUNT(id) AS count FROM contest.feedback WHERE DATEDIFF(NOW(), created) = 0");
$feedbackcount = isset($data[0]["count"]) ?$data[0]["count"] : 0;

$data = $db->query("SELECT COUNT(id) AS count FROM contest.feedback WHERE team = 227 AND DATEDIFF(NOW(), created) = 0");
$myfeedbackcount = isset($data[0]["count"]) ? $data[0]["count"] : 0;

die( json_encode( array( "data" =>
	array( array(
		"impressioncount" => $impressioncount,
		"itemcount" => $itemcount,
		"recommendationcount" => $recommendationcount,
		"feedbackcount" => $feedbackcount,
		"myfeedbackcount" => $myfeedbackcount
	)
))));