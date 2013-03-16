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
$res = $db->query("SELECT contest.item.id AS itemid, * FROM contest.feedback, contest.item WHERE feedback.target = item.id AND feedback.team = 227 ORDER BY feedback.created DESC LIMIT 20");
die( json_encode( array("data" => $res ) ) );
