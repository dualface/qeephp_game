<?php

namespace tests\fixture;

use qeeplay\mvc\App;

class TestApp extends App
{
    function __construct()
    {
        parent::__construct(__NAMESPACE__, __DIR__, false);
    }
}
