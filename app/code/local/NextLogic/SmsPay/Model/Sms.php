<?php

/**
 * Our test CC module adapter
 */
class NextLogic_SmsPay_Model_Sms extends Mage_Payment_Model_Method_Abstract
{
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    
    /**
     * Block type.
     * 
     * @var string [a-z0-9_]
     */
    protected $_formBlockType = 'smspay/form_sms';
    
    /**
     * unique internal payment method identifier
     *
     * @var string [a-z0-9_]
     */
    protected $_code = 'smspay';

    /**
     * Here are examples of flags that will determine functionality availability
     * of this module to be used by frontend and backend.
     *
     * @see all flags and their defaults in Mage_Payment_Model_Method_Abstract
     *
     * It is possible to have a custom dynamic logic by overloading
     * public function can* for each flag respectively
     */

    /**
     * Is this payment method a gateway (online auth/charge) ?
     */
    protected $_isGateway = true;

    /**
     * Can authorize online?
     */
    protected $_canAuthorize = true;

    /**
     * Can capture funds online?
     */
    protected $_canCapture = true;

    /**
     * Can capture partial amounts online?
     */
    protected $_canCapturePartial = false;

    /**
     * Can refund online?
     */
    protected $_canRefund = false;

    /**
     * Can void transactions online?
     */
    protected $_canVoid = true;

    /**
     * Can use this payment method in administration panel?
     */
    protected $_canUseInternal = true;

    /**
     * Can show this payment method as an option on checkout payment page?
     */
    protected $_canUseCheckout = true;

    /**
     * Is this payment method suitable for multi-shipping checkout?
     */
    protected $_canUseForMultishipping = true;

    /**
     * Can save credit card information for future processing?
     */
    protected $_canSaveCc = false;

    /**
     * Here you will need to implement authorize, capture and void public methods
     *
     * @see examples of transaction specific public methods such as
     * authorize, capture and void in Mage_Paygate_Model_Authorizenet
     */
    
    /**
     * This method is called if we are just authorising
     * a transaction
     * 
     * 
     */
    public function authorize(Varien_Object $payment, $amount)
    {
        if ($amount <= 0) {
            Mage::throwException( $this->_getHelper()->__('Invalid amount for authorization.') );
        }
        
        $this->_place( $payment, $amount, self::REQUEST_TYPE_AUTH_ONLY );
        
    }

    /**
     * this method is called if we are authorising AND
     * capturing a transaction
     */
    public function capture(Varien_Object $payment, $amount)
    {
        exit('INVALID');
    }

    /**
     * called if refunding
     */
    public function refund(Varien_Object $payment, $amount)
    {
        exit('_INVALID');
    }

    /**
     * called if voiding a payment
     */
    public function void(Varien_Object $payment)
    {
        exit('__INVALID');
    }
    
    /**
     * Assign data to \Mage_Payment_Model_Info for further use.
     * 
     * @param \Varien_Object $data
     * @return \NextLogic_SmsPay_Model_Sms
     */
    public function assignData($data)
    {
        if (!($data instanceof Varien_Object)) {
            $data = new Varien_Object($data);
        }
        $info = $this->getInfoInstance();
        $info->setPhoneCode($data->getPhoneCode())
        ->setPhoneNumber($data->getPhoneNumber());
        return $this;
    }
    
    /**
     * Validate the form.
     * 
     * @return \NextLogic_SmsPay_Model_Sms
     */
    public function validate()
    {
        parent::validate();
        
        $info = $this->getInfoInstance();
        
        $phone_code = $info->getPhoneCode();
        $phone_number = $info->getPhoneNumber();
        $available_phone_codes = explode( ',', $this->getConfigData('phone_code') );
        
        if( empty($phone_code) || empty($phone_number) ) {
            Mage::throwException( $this->_getHelper()->__('Phone code and number are required fields!') );
        }
        if( !is_numeric( $phone_code ) || preg_match( "/^[0-9]+$/", $phone_number ) === 0 ) {
            Mage::throwException( $this->_getHelper()->__('Invalid data provided!') );
        }
        if( !in_array( $phone_code, $available_phone_codes ) ) {
            Mage::throwException( $this->_getHelper()->__('Phone code inactive!') );
        }
        
        return $this;
    }
    
    /**
     * Place the order and make the API call.
     * 
     * @param Varien_Object $payment
     * @param int $amount
     * @param string $requestType
     * @return \NextLogic_SmsPay_Model_Sms
     */
    protected function _place( Varien_Object $payment, $amount, $requestType )
    {
        $payment->setAnetTransType($requestType);
        $payment->setAmount($amount);
        
        return $this;
    }
    
    protected function _getRequest()
    {
        $request = Mage::getModel('smspay/smspay_request');
        
        return $request;
    }


    /**
     * Prepare request to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @return Mage_Paygate_Model_Authorizenet_Request
     */
    protected function _buildRequest(Varien_Object $payment)
    {
        $order = $payment->getOrder();
        
        $this->setStore($order->getStoreId());
        
        $request = $this->_getRequest();
        
        return $request;
    }
}
