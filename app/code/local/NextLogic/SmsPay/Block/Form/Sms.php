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
        
        $mark = Mage::getConfig()->getBlockClassName( 'core/template' );
        $mark = new $mark;
        $mark->setTemplate( 'smspay/form/mark.phtml' )->setImageSrc( 'https://www.paypalobjects.com/en_US/i/logo/logo_BMLPP_90x34.gif' );
        
        $this->setTemplate('smspay/form/sms.phtml')->setMethodLabelAfterHtml( $mark->toHtml() );
    }
    
}