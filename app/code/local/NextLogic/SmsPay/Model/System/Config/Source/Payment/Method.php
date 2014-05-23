<?php

class NextLogic_SmsPay_Model_System_Config_Source_Payment_Method
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => 0,
            'label' => 'SMSpay Credit Card'
        );
        $options[] = array(
            'value' => 1,
            'label' => 'SMSpay CPA GAS'
        );
        
        return $options;
    }
}