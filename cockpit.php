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
<script type="text/javascript">
    /**
     * function to update an table
     */
    function performAjaxUpdates(id) {
        $('#' + id).dataTable().fnReloadAjax();
    }

	function updateTables() {
        performAjaxUpdates('numbers');
        performAjaxUpdates('latestrecommends');
        performAjaxUpdates('latestfeedbacks');
        setTimeout("updateTables()", 1000);
	}

    $(document).ready(function () {
        setTimeout("updateTables()", 1000);
	});
</script>

<script type="text/javascript">
    $(document).ready(function () {

        /**
         * running processes and daemons
         */
        $('#numbers').dataTable({
            "sDom": "r",
            "sAjaxSource":"api/numbers.php",
            "sAjaxDataProp":"data",
            "iDisplayLength":10,
            "aoColumns":[
                { "mDataProp":"impressioncount" },
                { "mDataProp":"itemcount" },
                { "mDataProp":"recommendationcount" },
                { "mDataProp":"feedbackcount" },
                { "mDataProp":"myfeedbackcount" }
            ]
        });
    });
</script>

<h3>Numbers</h3>

<table style="" id="numbers">
    <thead>
    <tr>
        <th>Impressions</th>
        <th>Items</th>
        <th>Recommendations</th>
        <th>Feedback total</th>
        <th>My Feedback</th>
    </tr>
    </thead>
    <tbody></tbody>
</table>


<script type="text/javascript">
    $(document).ready(function () {

        /**
         * running processes and daemons
         */
        $('#latestrecommends').dataTable({
            "sDom": "r",
            "sAjaxSource":"api/latestrecommends.php",
            "sAjaxDataProp":"data",
            "iDisplayLength":10,
            "aoColumns":[
                { "mDataProp":"client" },
                {
                    "mData":function (data, type) {
                        return data.url.length == 0 ? data.id : "<a href='" + data.url + "'>" + data.id + "</a>" ;
                    }
                },
                { "mDataProp":"sourcetitle", "sWidth": "38%" },
                {
                    "mData":function (data, type) {
                        return data.url.length == 0 ? data.id : "<a href='" + data.url + "'>" + data.id + "</a>" ;
                    }
                },
                { "mDataProp":"title", "sWidth": "38%" },
                { "mDataProp":"created", "sWidth": "10%" }
            ]
        });
    });
</script>

<h3>Latest Reccomends</h3>

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


<script type="text/javascript">
    $(document).ready(function () {

        /**
         * running processes and daemons
         */
        $('#latestfeedbacks').dataTable({
            "sDom": "r",
             "sAjaxSource":"api/latestfeedbacks.php",
            "sAjaxDataProp":"data",
            "iDisplayLength":10,
            "aoColumns":[
                { "mDataProp":"id"},
                { "mDataProp":"client" },
                { "mDataProp":"source" },
                { "mDataProp":"title", "sWidth": 600 },
                {
                    "mData":function (data, type) {
                        return data.url.length == 0 ? data.id : "<a href='" + data.url + "'>" + data.id + "</a>" ;
                    }
                },
                { "mDataProp":"domain" },
                { "mDataProp":"team" },
                { "mDataProp":"created" }
            ]
        });
    });
</script>

<h3>Latest Feedbacks</h3>

<table style="" id="latestfeedbacks">
    <thead>
    <tr>
        <th>ID</th>
        <th>Client</th>
        <th>Source</th>
        <th>Target</th>
        <th>Target ID</th>
        <th>Domain</th>
        <th>Team</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>

