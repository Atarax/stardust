<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 3:32 PM
 * To change this template use File | Settings | File Templates.
 */
require_once("../config.php");
$db = DatabaseManager::getInstace();
$res = $db->query("SELECT item.url, item.id AS itemid, feedback.*, item.id, item.title FROM contest.feedback, contest.item WHERE feedback.target = item.id AND feedback.team = 227 ORDER BY feedback.created DESC LIMIT 10");
die( json_encode( array("data" => $res ) ) );
