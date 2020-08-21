<?php

define('ROOT', __DIR__);

require ('vendor/autoload.php');

use App\Search;

$search = new Search();
$search->run();
