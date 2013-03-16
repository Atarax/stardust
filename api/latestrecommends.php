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
$res = $db->query("SELECT * FROM contest.recommendation, contest.item WHERE recommendation.item = item.id ORDER BY recommendation.created DESC LIMIT 30");
die( json_encode( array("data" => $res ) ) );
