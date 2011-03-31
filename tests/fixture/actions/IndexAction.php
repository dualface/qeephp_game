<?php

namespace tests\fixture\actions;

use qeeplay\mvc\BaseAction;

class IndexAction extends BaseAction
{
    function execute()
    {
        echo 'indexAction';
    }
}
