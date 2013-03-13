<?php

require_once("config.php");
$item = new Item();
$item->id = 123;
$item->domain = 456;
$item->recommendable = 7;

$item->save();