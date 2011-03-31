<?php

namespace tests\fixture\actions\tests;

use qeeplay\mvc\BaseAction;

class EmptyAction extends BaseAction
{
    function execute()
    {
        echo 'tests/emptyAction';
    }
}
