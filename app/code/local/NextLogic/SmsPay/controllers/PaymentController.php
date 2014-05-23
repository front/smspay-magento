<?php

class NextLogic_SmsPay_IndexController extends Mage_Core_Controller_Front_Action
{
    public function successAction()
    {
        echo 'success';
    }
    
    public function failureAction()
    {
        echo 'failure';
    }
}