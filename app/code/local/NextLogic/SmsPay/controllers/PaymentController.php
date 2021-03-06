<?php

class NextLogic_SmsPay_PaymentController extends Mage_Core_Controller_Front_Action
{
    const PAYMENT_STATUS_COMPLETED = 'COMPLETED';
    
    public function successAction()
    {
        if( !$this->getRequest()->isPost() ) {
            return false;
        }
        
        $reference = $this->getRequest()->getPost( 'reference' );
        $phone = $this->getRequest()->getPost( 'phone' );
        $merchantId = $this->getRequest()->getPost( 'merchantId' );
        $type = $this->getRequest()->getPost( 'type' );
        $status = $this->getRequest()->getPost( 'status' );
        $description = $this->getRequest()->getPost( 'description' );
        $invoice = $this->getRequest()->getPost( 'invoice' );
        $amount = $this->getRequest()->getPost( 'amount' );
        $shipping = $this->getRequest()->getPost( 'shipping' );
        $currency = $this->getRequest()->getPost( 'currency' );
        $payment = $this->getRequest()->getPost( 'payment' );
        $fee = $this->getRequest()->getPost( 'fee' );
        $createdAt = $this->getRequest()->getPost( 'createdAt' );
        $updatedAt = $this->getRequest()->getPost( 'updatedAt' );
        $cancelReason = $this->getRequest()->getPost( 'cancelReason' );
        
        if ( !is_numeric( $invoice ) ) {
            return false;
        }
        $order = Mage::getModel('sales/order')->loadByIncrementId( $invoice );
                
        switch ( $status ) {
            
            case self::PAYMENT_STATUS_COMPLETED:
                
        		$order->setState( Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.' );
        		
        		$order->sendNewOrderEmail();
        		$order->setEmailSent( true );
        		
        		$order->save();
        	
        		Mage::getSingleton('checkout/session')->unsQuoteId();
                
                echo 'ACCEPTED';
                
                return true;
        }
        
        
    }
    
    public function failureAction()
    {
        if ( $this->_getSession()->getLastRealOrderId() ) {
            $order = Mage::getModel( 'sales/order' )->loadByIncrementId( $this->_getSession()->getLastRealOrderId() );
            if($order->getId()) {
                $order->cancel()->setState( Mage_Sales_Model_Order::STATE_CANCELED, true, 'Gateway has declined the payment.' )->save();
            }
        }
    }
    
    private function _getSession()
    {
        return Mage::getSingleton( 'checkout/session' );
    }
}