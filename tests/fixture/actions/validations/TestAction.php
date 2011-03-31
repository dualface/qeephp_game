<?php

namespace tests\fixture\actions\validations;

use qeeplay\mvc\BaseAction;

/**
 * 用于测试 Action 数据过滤
 */
class TestAction extends BaseAction
{

    function execute()
    {
        $this->result[] = 'execute';
    }

    protected function _validate_input()
    {
        if (parent::_validate_input())
        {
            $this->result[] = '_validate_input';
            if (get('failed') == 'input')
            {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function _validate_output()
    {
        if (parent::_validate_output())
        {
            $this->result[] = '_validate_output';
            if (get('failed') == 'output')
            {
                return false;
            }
            return true;
        }
        return false;
    }

    protected function _on_validate_input_failed()
    {
        $this->result[] = '_on_validate_input_failed';
    }

    protected function _on_validate_output_failed()
    {
        $this->result[] = '_on_validate_output_failed';
    }

    protected function _before_execute()
    {
        if (parent::_before_execute())
        {
            $this->result = array();
            return true;
        }
        return false;
    }
}

