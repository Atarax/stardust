<?php

require_once("config.php");
$item = new Item();
$item->id = 123;
$item->domain = 456;
$item->recommendable = 7;
$item->category = 2;
$item->text = "foo";
$item->url = "http://foo.bar";
$item->save();

$impression = new Impression();
$impression->client = 333;
$impression->item = 123;
$impression->domain = 456;
$impression->id = 999;
$impression->save();

$feedback = new Feedback();
$feedback->id = 872;
$feedback->client = 333;
$feedback->source = 123;
$feedback->target = 245;
$feedback->save();