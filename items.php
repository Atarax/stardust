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
	$res = $db->query("SELECT * FROM contest.item");
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
        $('#items').dataTable({
            "bProcessing":true,
            "sAjaxSource":"items.php?ajax=1",
            "sAjaxDataProp":"data",
            "iDisplayLength":25,
            "aoColumns":[
                { "mDataProp":"id" },
                { "mDataProp":"domain" },
                { "mDataProp":"category" },
                { "mDataProp":"title" },
                { "mDataProp":"text" },
                { "mDataProp":"url" },
                { "mDataProp":"img" },
                { "mDataProp":"recommendable" },
                { "mDataProp":"created" }
            ]
        });
});
</script>

<h2>Items</h2>

<table style="" id="items">
    <thead>
    <tr>
        <th>ID</th>
        <th>Domain</th>
        <th>Category</th>
        <th>Title</th>
        <th>Text</th>
        <th>Url</th>
        <th>Image</th>
        <th>Recommendable</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>