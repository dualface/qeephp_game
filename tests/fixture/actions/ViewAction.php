<?php

namespace tests\fixture\actions;

use qeeplay\mvc\BaseAction;

class ViewAction extends BaseAction
{
    function execute()
    {
        $this->view();
    }
}
