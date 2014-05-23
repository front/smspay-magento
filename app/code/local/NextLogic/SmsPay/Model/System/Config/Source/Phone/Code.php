<?php

class NextLogic_SmsPay_Model_System_Config_Source_Phone_Code
{
    public function toOptionArray()
    {
        $options = array();
        $options[] = array(
            'value' => 35,
            'label' => '+35'
        );
        $options[] = array(
            'value' => 46,
            'label' => '+46'
        );
        $options[] = array(
            'value' => 47,
            'label' => '+47'
        );
        
        return $options;
    }
}