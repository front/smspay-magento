<?php

/**
 * Our test CC module adapter
 */
class NextLogic_SmsPay_Model_Sms extends Mage_Payment_Model_Method_Abstract
{
    /**
     * Live and test URLs
     * 
     * Payments
     * Login
     */
    const REQUEST_PAYMENTS_LIVE_URL = 'http://api.smspay.devz.no/v1/payments';
    const REQUEST_PAYMENTS_TEST_URL = 'http://api.smspay.devz.no/v1/payments';
    
    const REQUEST_LOGIN_LIVE_URL = 'http://api.smspay.devz.no/v1/login';
    const REQUEST_LOGIN_TEST_URL = 'http://api.smspay.devz.no/v1/login';
    
    // API response status codes
    const API_RESPONSE_STATUS_CODE_ERROR = 400;
    const API_RESPONSE_STATUS_CODE_SUCCESS = 200;
    
    
    // Authorize only
    const REQUEST_TYPE_AUTH_ONLY = 'AUTH_ONLY';
    
    // Response codes
    const RESPONSE_CODE_ERROR = -1;
    const RESPONSE_CODE_APPROVED = 1;
    
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
    public function authorize( Varien_Object $payment, $amount )
    {
        if ( $amount <= 0 ) {
            Mage::throwException( $this->_getHelper()->__( 'Invalid amount for authorization.' ) );
        }
        
        $this->_place( $payment, $amount, self::REQUEST_TYPE_AUTH_ONLY );
        $payment->setSkipTransactionCreation( true );
        
        return $this; 
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
     * Get the success URL callback.
     * 
     * @return string
     */
    public function getSuccessUrl()
    {
        return Mage::getUrl( 'smspay/payment/success' );
    }
    
    /**
     * Get the failure URL callback.
     * 
     * @return string
     */
    public function getFailureUrl()
    {
        return Mage::getUrl( 'smspay/payment/failure' );
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
        
        $request = $this->_buildRequest( $payment );
        $result = $this->_postRequest( $request );
        
        switch ($requestType) {
            case self::REQUEST_TYPE_AUTH_ONLY:
                $newTransactionType = Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH;
                $defaultExceptionMessage = $this->_getHelper()->__( 'Payment authorization error.' );
                break;
        }
        
        switch ( $result->getResponseCode() ) {
            case self::RESPONSE_CODE_APPROVED:
                
                $this->_addTransaction( 
                    $payment, 
                    $result->getTransactionId(),
                    $newTransactionType, 
                    array(), 
                    array(), 
                    $this->_getHelper()->getTransactionMessage( $payment, $requestType, $result->getTransactionId(), $amount )
                );
                
                return $this;
            default:
                Mage::throwException( $defaultExceptionMessage );
        }
        
        return $this;
    }
    
    /**
     * Prepare request to gateway
     *
     * @param Mage_Payment_Model_Info $payment
     * @return Mage_Paygate_Model_Authorizenet_Request
     */
    protected function _buildRequest( Varien_Object $payment )
    {
        $order = $payment->getOrder();
        
        $this->setStore( $order->getStoreId() );
        
        $request = $this->_getRequest();
        
        $request->setPhone( $payment->getPhoneCode() . $payment->getPhoneNumber() );
        $request->setInvoice( $order->getIncrementId() );
        $request->setCurrency( $order->getBaseCurrencyCode() );
        $request->setMerchant( $this->getConfigData( 'merchant_id' ) );
        $request->setDescription(  );
        $request->setShipping( (int)($order->getShippingAmount() * 100) );
        
        foreach( $order->getAllItems() as $key => $item ) {
            $itemNumber = $key + 1;
            $request->{ 'setItemNumber_' . $itemNumber }( $item->getSku() );
            $request->{ 'setItemName_' . $itemNumber }( $item->getName() );
            $request->{ 'setAmount_' . $itemNumber }( (int)($item->getPrice() * 100) );
            $request->{ 'setQuantity_' . $itemNumber }( $item->getQtyOrdered() );
            $request->{ 'setShipping_' . $itemNumber }( 0 );
        }
        
        $request->setSuccessUrl( $this->getSuccessUrl() );
        $request->setFailureUrl( $this->getFailureUrl() );
        
        return $request;
    }
    
    /**
     * 
     * @param NextLogic_SmsPay_Model_Smspay_Request $request
     * @return NextLogic_SmsPay_Model_Smspay_Result
     */
    protected function _postRequest( Varien_Object $request )
    {
        $loginResult = $this->_loginPostRequest();
        
        $result = $this->_getResult();
        
        $client = new Varien_Http_Client();

        $testMode = $this->getConfigData( 'test_mode' );
        $client->setUri( $testMode == 'yes' ? self::REQUEST_PAYMENTS_TEST_URL : self::REQUEST_PAYMENTS_LIVE_URL );
        $client->setConfig( array(
            'maxredirects' => 0,
            'timeout' => 30,
            //'ssltransport' => 'tcp',
        ) );
        $client->setParameterPost( $request->getData() );
        $client->setMethod( Zend_Http_Client::POST );
        $client->setHeaders( 'Authorization', 'Bearer ' . $loginResult->getToken() );
        
        try {
            $response = $client->request();
        } catch ( Exception $e ) {
            $result->setResponseCode( self::RESPONSE_CODE_ERROR );
            
            Mage::throwException( $this->_getHelper()->__( 'Gateway error: %s', $e->getMessage() ) );
        }
        
        $responseBody = json_decode( $response->getBody() );
        
        if ( isset( $responseBody->statusCode ) ) {
            switch( $responseBody->statusCode ) {
                case self::API_RESPONSE_STATUS_CODE_ERROR:
                    
                    $result->setResponseCode( self::RESPONSE_CODE_ERROR );
                    
                    $this->_log( $responseBody->error . ': ' . $responseBody->message );
                    
                    break;
            }
            
            Mage::throwException( $this->_getHelper()->__( 'Gateway error. Please contact the administrator.' ) );
        } else {
            
            $result->setResponseCode( self::RESPONSE_CODE_APPROVED )
                ->setTransactionId( $responseBody->reference );
                        
        }
        
        return $result;
    }
    
    protected function _buildLoginRequest()
    {
        $request = $this->_getRequest();
        
        $request->setUser( 'magentoshop' );
        $request->setPassword( 'magentoshop' );
        
        return $request;
    }
    
    /**
     * Login the user.
     * 
     * @return NextLogic_SmsPay_Model_Smspay_Result
     */
    protected function _loginPostRequest()
    {
        $request = $this->_buildLoginRequest();
        
        $result = $this->_getResult();
        
        $client = new Varien_Http_Client();

        $test_mode = $this->getConfigData( 'test_mode' );
        $client->setUri( $test_mode == 'yes' ? self::REQUEST_LOGIN_TEST_URL : self::REQUEST_LOGIN_LIVE_URL );
        $client->setConfig( array(
            'maxredirects' => 0,
            'timeout' => 30,
            //'ssltransport' => 'tcp',
        ) );
        $client->setParameterPost( $request->getData() );
        $client->setMethod( Zend_Http_Client::POST );
        
        try {
            $response = $client->request();
        } catch ( Exception $e ) {
            $result->setResponseCode( -1 )
                ->setResponseReasonCode( $e->getCode() )
                ->setResponseReasonText( $e->getMessage() );
            
            Mage::throwException( $this->_getHelper()->__( 'Gateway error: %s', $e->getMessage() ) );
        }
        
        // JSON format response
        $responseBody = json_decode( $response->getBody() );
        
        if ( $responseBody ) {
            $result->setToken( $responseBody->token );
        } else {
            Mage::throwException( $this->_getHelper()->__( 'Error in payment gateway.' ) );
        }
        
        return $result;
    }
    
    /**
     * Add payment transaction
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     * @param string $transactionId
     * @param string $transactionType
     * @param array $transactionDetails
     * @param array $transactionAdditionalInfo
     * @return null|Mage_Sales_Model_Order_Payment_Transaction
     */
    protected function _addTransaction( Mage_Sales_Model_Order_Payment $payment, $transactionId, $transactionType, array $transactionDetails = array(), array $transactionAdditionalInfo = array(), $message = false )
    {
        $payment->setTransactionId( $transactionId );
        $payment->resetTransactionAdditionalInfo();
        foreach ( $transactionDetails as $key => $value ) {
            $payment->setData( $key, $value );
        }
        foreach ( $transactionAdditionalInfo as $key => $value ) {
            $payment->setTransactionAdditionalInfo( $key, $value );
        }
        $transaction = $payment->addTransaction( $transactionType, null, false , $message );
        foreach ( $transactionDetails as $key => $value ) {
            $payment->unsetData( $key );
        }
        $payment->unsLastTransId();

        /**
         * It for self using
         */
        $transaction->setMessage( $message );

        return $transaction;
    }
    
    /**
     * 
     */
    protected function _getHelper()
    {
        return Mage::helper( 'smspay' );
    }
    
    /**
     * 
     * @return NextLogic_SmsPay_Model_Smspay_Request
     */
    protected function _getRequest()
    {
        $request = Mage::getModel( 'smspay/smspay_request' );
        
        return $request;
    }
    
    /**
     * 
     * @return NextLogic_SmsPay_Model_Smspay_Result
     */
    protected function _getResult()
    {
        $result = Mage::getModel( 'smspay/smspay_result' );
        
        return $result;
    }
    
    /**
     * 
     * @return Mage_Customer_Model_Session
     */
    protected function _getSession()
    {
        $session = Mage::getSingleton('customer/session');
        
        return $session;
    }
    
    private function _log( $message, $level = null )
    {
        Mage::log( $message, $level, 'smspay.log' );
    }
}
