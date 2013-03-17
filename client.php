<?php

if( !isset($_GET["client"]) ) {
	die("Please supply client as get parameter!");
}
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/17/13
 * Time: 1:55 PM
 * To change this template use File | Settings | File Templates.
 */
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
            "sDom": "r",
            "sAjaxSource":"api/impressionsbyclient.php?client=" + <?= $_GET["client"] ?>,
            "sAjaxDataProp":"data",
            "iDisplayLength":10,
            "aoColumns":[
                {
                    "mData":function (data, type) {
                        return data.url == null ? data.id : "<a href='" + data.url + "'>" + data.id + "</a>" ;
                    }
                },
                { "mDataProp":"title", "sWidth": "58%" },
                { "mDataProp":"domain" },
                { "mDataProp":"created", "sWidth": "10%" }
            ]
        });
    });
</script>

<h3>Impressions</h3>

<table style="" id="impressions">
    <thead>
    <tr>
        <th>Item ID</th>
        <th>Item Title</th>
        <th>Domain</th>
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
        $('#recommendations').dataTable({
            "sDom": "r",
            "sAjaxSource":"api/recommendationsbyclient.php?client=" + <?= $_GET["client"] ?>,
            "sAjaxDataProp":"data",
            "iDisplayLength":10,
            "aoColumns":[
                {
                    "mData": function (data, type) {
                        return data.url == null ? data.itemid : "<a href='" + data.url + "'>" + data.itemid + "</a>" ;
                    }
                },
                { "mDataProp":"title", "sWidth": "85%" },
                { "mDataProp":"domain" },
                { "mDataProp":"recommender" },
                { "mDataProp":"created", "sWidth": "10%" }
            ]
        });
    });
</script>

<h3>Reccomends</h3>

<table style="" id="recommendations">
    <thead>
    <tr>
        <th>Item ID</th>
        <th>Item Title</th>
        <th>Domain</th>
        <th>R</th>
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
        $('#feedbacks').dataTable({
            "sDom": "r",
            "sAjaxSource":"api/feedbacksbyclient.php?client=" + <?= $_GET["client"] ?>,
            "sAjaxDataProp":"data",
            "iDisplayLength":10,
            "aoColumns":[
                {
                    "mData": function (data, type) {
                        return data.url == null ? data.itemid : "<a href='" + data.url + "'>" + data.itemid + "</a>" ;
                    }
                },
                { "mDataProp":"title", "sWidth": "85%" },
                { "mDataProp":"created", "sWidth": "10%" }
            ]
        });
    });
</script>

<h3>Feedbacks</h3>

<table style="" id="feedbacks">
    <thead>
    <tr>
        <th>Item ID</th>
        <th>Item Title</th>
        <th>Created</th>
    </tr>
    </thead>
    <tbody></tbody>
</table><br/>
