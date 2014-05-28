<?php

class NextLogic_SmsPay_Helper_Data extends Mage_Payment_Helper_Data
{
    
    /**
     * Return message for gateway transaction request
     *
     * @param  Mage_Payment_Model_Info $payment
     * @param  string $requestType
     * @param  string $transactionId
     * @param  float $amount
     * @param  string $exception
     * @param  string $additionalMessage Custom message, which will be added to the end of generated message
     * @return bool|string
     */
    public function getTransactionMessage( $payment, $requestType, $transactionId, $amount, $exception = false, $additionalMessage = false )
    {
        $operation = $this->_getOperation( $requestType );

        if ( !$operation ) {
            return false;
        }
        
        if ( $amount ) {
            $amount = $this->__( 'Amount %s,', $this->_formatPrice( $payment, $amount ) );
        }
        
        if ( $exception ) {
            $result = $this->__( 'failed' );
        } else {
            $result = $this->__( 'successful' );
        }
        
        $pattern = '%s %s - %s.';
        $texts = array( $amount, $operation, $result );
        
        if ( !is_null( $transactionId ) ) {
            $pattern .= ' %s.';
            $texts[] = $this->__('SmsPay Transaction ID %s', $transactionId);
        }
        
        if ( $additionalMessage ) {
            $pattern .= ' %s.';
            $texts[] = $additionalMessage;
        }
        $pattern .= ' %s';
        $texts[] = $exception;
        
        return call_user_func_array( array($this, '__'), array_merge( array($pattern), $texts ) );
    }
    
    /**
     * Return operation name for request type
     *
     * @param  string $requestType
     * @return bool|string
     */
    protected function _getOperation($requestType)
    {
        switch ( $requestType ) {
            case NextLogic_SmsPay_Model_Sms::REQUEST_TYPE_AUTH_ONLY:
                return $this->__('authorize');
            default:
                return false;
        }
    }
    
    /**
     * Format price with currency sign.
     * 
     * @param  Mage_Payment_Model_Info $payment
     * @param  float $amount
     * @return string
     */
    protected function _formatPrice( $payment, $amount )
    {
        return $payment->getOrder()->getBaseCurrency()->formatTxt( $amount );
    }
    
}
