<?php

namespace tests\fixture\actions\tests;

use qeeplay\mvc\BaseAction;

class ViewAction extends BaseAction
{
    function execute()
    {
        $this->view();
    }
}
