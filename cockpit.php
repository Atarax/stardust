<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 3:15 PM
 * To change this template use File | Settings | File Templates.
 */
require_once("config.php");

$db = new DatabaseManager();
$db->connect();

$data = $db->query("SELECT COUNT(id) AS count FROM contest.impression");
$impressioncount = $data[0]["count"];

$data = $db->query("SELECT COUNT(id) AS count FROM contest.item");
$itemcount = $data[0]["count"];

$data = $db->query("SELECT COUNT(id) AS count FROM contest.recommendation");
$recommendationcount = $data[0]["count"];

$data = $db->query("SELECT COUNT(id) AS count FROM contest.feedback");
$feedbackcount = $data[0]["count"];

$data = $db->query("SELECT COUNT(id) AS count FROM contest.feedback WHERE team = 227");
$myfeedbackcount = $data[0]["count"];

?>
<link rel="stylesheet" type="text/css" href="js/DataTables-1.9.3/media/css/jquery.dataTables.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.fnReloadAjax.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.rowGrouping.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.fnGetColumnData.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.helperFunctions.js"></script>

<h3>Numbers:</h3>
<div id="numbers">
	Impressions: <?= $impressioncount ?><br>
	Items: <?= $itemcount ?><br>
	Recommendations: <?= $recommendationcount ?><br>
	Feedbacks: <?= $feedbackcount ?><br>
	My Feedbacks: <?= $myfeedbackcount ?><br>
</div>


<script type="text/javascript">
    $(document).ready(function () {

        /**
         * running processes and daemons
         */
        $('#latestrecommends').dataTable({
            "bProcessing":true,
            "sAjaxSource":"api/latestrecommends.php",
            "sAjaxDataProp":"data",
            "iDisplayLength":25,
            "aoColumns":[
                { "mDataProp":"client" },
                { "mDataProp":"sourceid" },
                { "mDataProp":"sourcetitle" },
                { "mDataProp":"id" },
                { "mDataProp":"title" },
                { "mDataProp":"created" }
            ]
        });
    });
</script>

<h2>Latest Reccomends</h2>

<table style="" id="latestrecommends">
    <thead>
    <tr>
        <th>Client</th>
        <th>Source ID</th>
        <th>Source Title</th>
        <th>Target ID</th>
        <th>Target Title</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>
<br>


<script type="text/javascript">
    $(document).ready(function () {

        /**
         * running processes and daemons
         */
        $('#latestfeedbacks').dataTable({
            "bProcessing":true,
            "sAjaxSource":"api/latestfeedbacks.php",
            "sAjaxDataProp":"data",
            "iDisplayLength":25,
            "aoColumns":[
                { "mDataProp":"id" },
                { "mDataProp":"client" },
                { "mDataProp":"source" },
                { "mDataProp":"title" },
                { "mDataProp":"domain" },
                { "mDataProp":"team" },
                { "mDataProp":"created" }
            ]
        });
    });
</script>

<h2>Latest Feedbacks</h2>

<table style="" id="latestfeedbacks">
    <thead>
    <tr>
        <th>ID</th>
        <th>Client</th>
        <th>Source</th>
        <th>Target</th>
        <th>Domain</th>
        <th>Team</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>

