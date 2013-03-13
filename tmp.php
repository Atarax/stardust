<?php

require_once("config.php");
$item = new Item();
$item->id = 0;
$item->domain = 0;
$item->recommendable = 0;

$item->save();