<?php

namespace tests\fixture\actions;

use qeeplay\mvc\BaseAction;

class HelpAction extends BaseAction
{
    function execute()
    {
        echo 'helpAction';
    }
}
