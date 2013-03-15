<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/14/13
 * Time: 3:45 PM
 * To change this template use File | Settings | File Templates.
 */
require_once("config.php");

if( isset($_GET["ajax"]) ) {
	$db = new DatabaseManager();
	$res = $db->query("SELECT * FROM contest.feedback WHERE team = 227 ORDER BY CREATED DESC");
	die( json_encode( array("data" => $res ) ) );
}

?>
<link rel="stylesheet" type="text/css" href="js/DataTables-1.9.3/media/css/jquery.dataTables.css">
<script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/jquery.dataTables.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.fnReloadAjax.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.rowGrouping.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.fnGetColumnData.js"></script>
<script type="text/javascript" src="js/DataTables-1.9.3/media/js/dataTables.helperFunctions.js"></script>

<script type="text/javascript">
    $(document).ready(function () {

        /**
         * running processes and daemons
         */
        $('#feedback').dataTable({
            "bProcessing":true,
            "sAjaxSource":"myfeedback.php?ajax=1",
            "sAjaxDataProp":"data",
            "iDisplayLength":25,
            "aoColumns":[
                { "mDataProp":"id" },
                { "mDataProp":"client" },
                { "mDataProp":"source" },
                { "mDataProp":"target" },
                { "mDataProp":"domain" },
                { "mDataProp":"created" }
            ]
        });
    });
</script>

<h2>My Successfull Recommendations</h2>

<table style="" id="feedback">
    <thead>
    <tr>
        <th>ID</th>
        <th>Client</th>
        <th>Source</th>
        <th>Target</th>
        <th>Domain</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>