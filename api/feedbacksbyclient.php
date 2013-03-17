<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 3:32 PM
 * To change this template use File | Settings | File Templates.
 */
require_once("../config.php");
$db = new DatabaseManager();
$client = $_GET["client"];
$res = $db->query("SELECT item.url, item.id AS itemid, feedback.id, feedback.target, feedback.created, item.title, item.domain FROM contest.feedback, contest.item WHERE feedback.client = ".$client." AND  feedback.target = item.id ORDER BY feedback.created DESC");
die( json_encode( array("data" => $res ) ) );
