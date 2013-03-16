<?php
/**
 * Created by JetBrains PhpStorm.
 * User: atarax
 * Date: 3/16/13
 * Time: 10:36 AM
 * To change this template use File | Settings | File Templates.
 */

require_once("config.php");

$reader = new RssReader();
$reader->read('http://www.hartware.net/xml/news.rdf');