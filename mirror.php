<?php
require_once("config.php");

$mirror = new MessageMirror();

if( isset($_GET["mirror"]) && !empty($_GET["mirror"]) ) {
	$mirror->setMirror($_GET["mirror"]);
	echo "Mirror set to: ".$_GET["mirror"];
}
else if( isset($_GET["disable"]) ) {
	$mirror->setMirror();
	echo "Mirror function disabled!";
}
else {
	$currentMirror = $mirror->getMirror();
	echo "Current Mirror: ".( empty($currentMirror) ? "None" : $mirror->getMirror() );
}