<?php

class NextLogic_SmsPay_Block_Form_Sms extends Mage_Payment_Block_Form
{
    /**
     * Available locales for content URL generation
     *
     * @var array
     */
    protected $_supportedInfoLocales = array('en');

    /**
     * Default locale for content URL generation
     *
     * @var string
     */
    protected $_defaultInfoLocale = 'en';

    /**
     * Constructor. Set template.
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('smspay/form/sms.phtml');
    }
    
}