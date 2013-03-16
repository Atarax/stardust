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
$res = $db->query("SELECT r1.*, item.title AS sourcetitle, item.url AS sourceurl, r1.source AS sourceid FROM (SELECT recommendation.client, recommendation.source, recommendation.created, item.title, item.id, item.url FROM contest.recommendation, contest.item WHERE recommendation.item = item.id ORDER BY recommendation.created DESC LIMIT 10)r1 LEFT JOIN contest.item ON item.id = r1.source");
die( json_encode( array("data" => $res ) ) );
