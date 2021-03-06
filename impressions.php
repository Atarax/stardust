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
	$db = DatabaseManager::getInstace();
	$res = $db->query("SELECT * FROM contest.impression");
	//var_dump( array("data" => $res) );
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
        $('#impressions').dataTable({
            "bProcessing":true,
            "sAjaxSource":"impressions.php?ajax=1",
            "sAjaxDataProp":"data",
            "iDisplayLength":25,
            "aoColumns":[
                { "mDataProp":"id" },
                { "mDataProp":"client" },
                { "mDataProp":"domain" },
                { "mDataProp":"item" }
            ]
        });
    });
</script>

<h2>Impressions</h2>

<table style="" id="impressions">
    <thead>
    <tr>
        <th>ID</th>
        <th>Client</th>
        <th>Domain</th>
        <th>Item</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>