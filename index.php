<?php

require_once __DIR__.'/vendor/autoload.php';

use Controllers\TestController;

function run()
{
    (new TestController())->index();
}


run();