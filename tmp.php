<?php

require_once("config.php");


$t1 = microtime(true);
sleep(3);
$t2 = microtime(true);
echo ( round($t2-$t1, 3) )." sec".PHP_EOL;