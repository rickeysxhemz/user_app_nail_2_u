<?php

require('constants/dev_constants.php');
require('constants/production_constants.php');
require('constants/test_constants.php');

$host = env('APP_ENV');
$constants = [];

if ($host === 'local') {
    $constants = $dev_constants;
}

if ($host === 'testing') {
    $constants = $test_constants;
}

if ($host === 'production') {
    $constants = $production_constants;
}

return $constants;
