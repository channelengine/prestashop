<?php

namespace ChannelEngineApiClient\Models {
	
	use ChannelEngineApiClient\Models\BaseModel;
	use ChannelEngineApiClient\Helpers\Collection;

    class Order extends BaseModel {
		
		public static $typeMap = array(
			'billingAddress' => 'ChannelEngineApiClient\Models\Address',
			'cancellations' => 'ChannelEngineApiClient\Helpers\Collection(ChannelEngineApiClient\Models\Cancellation)',
			'extraData' => 'ChannelEngineApiClient\Helpers\Collection(ChannelEngineApiClient\Models\OrderExtraDataItem)',
			'shippingAddress' => 'ChannelEngineApiClient\Models\Address',
			'lines' => 'ChannelEngineApiClient\Helpers\Collection(ChannelEngineApiClient\Models\OrderLine)',
			'shipments' => 'ChannelEngineApiClient\Helpers\Collection(ChannelEngineApiClient\Models\Shipment)',		
		);
		
		protected $phone;
		protected $email;
		protected $cocNo;
		protected $vatNo;
		protected $paymentMethod;
		protected $orderDate; 
		protected $createdAt;
		protected $updatedAt;
		protected $channelId;
		protected $channelOrderNo;
		protected $channelCustomerNo;
		protected $billingAddress;
		protected $cancellations;
		protected $channelName;
		protected $doSendMails;
		protected $canShipPartialOrderLines;
		
		protected $merchantId;
		protected $merchantOrderNo;
		protected $shippingCostsInclVat;
		protected $shippingCostsVat;
		protected $subTotalInclVat; 
		protected $subTotalVat; 
		protected $totalInclVat; 
		protected $totalVat; 
		protected $refundInclVat; 
		protected $refundExclVat; 		
		protected $extraData;
		protected $shippingAddress;
		protected $status;
		protected $closedDate;
		protected $lines;
		protected $shipments;
		protected $maxVatRate;	
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	

			$this->lines = new Collection('ChannelEngineApiClient\Models\OrderLine');
		}
		
		function setPhone($phone) { $this->phone = $phone; }
		function getPhone() { return $this->phone; }
		
		function setEmail($email) { $this->email = $email; }
		function getEmail() { return $this->email; }
		
		function setCocNo($cocNo) { $this->cocNo = $cocNo; }
		function getCocNo() { return $this->cocNo; }
		
		function setVatNo($vatNo) { $this->vatNo = $vatNo; }
		function getVatNo() { return $this->vatNo; }
		
		function setPaymentMethod($paymentMethod) { $this->paymentMethod = $paymentMethod; }
		function getPaymentMethod() { return $this->paymentMethod; }
		
		function setOrderDate($orderDate) { $this->orderDate = $orderDate; }
		function getOrderDate() { return $this->orderDate; }
  
		function setCreatedAt( $createdAt) { $this->createdAt = $createdAt; }
		function getCreatedAt() { return $this->createdAt; }
		
		function setUpdatedAt( $updatedAt) { $this->updatedAt = $updatedAt; }
		function getUpdatedAt() { return $this->updatedAt; }
		
		function setChannelId($channelId) { $this->channelId = $channelId; }
		function getChannelId() { return $this->channelId; }
		
		function setChannelOrderNo($channelOrderNo) { $this->channelOrderNo = $channelOrderNo; }
		function getChannelOrderNo() { return $this->channelOrderNo; }
		
		function setChannelCustomerNo($channelCustomerNo) { $this->channelCustomerNo = $channelCustomerNo; }
		function getChannelCustomerNo() { return $this->channelCustomerNo; }
		
		function setBillingAddress(Address $billingAddress) { $this->billingAddress = $billingAddress; }
		function getBillingAddress() { return $this->billingAddress; }
		
		function setCancellations($cancellations) { $this->cancellations = $cancellations; }
		function getCancellations() { return $this->cancellations; }
		
		function setChannelName($channelName) { $this->channelName = $channelName; }
		function getChannelName() { return $this->channelName; }

		function setDoSendMails($doSendMails) { $this->doSendMails = $doSendMails; }
		function getDoSendMails() { return $this->doSendMails; }

		function setCanShipPartialOrderLines($canShipPartialOrderLines) { $this->canShipPartialOrderLines = $canShipPartialOrderLines; }
		function getCanShipPartialOrderLines() { return $this->canShipPartialOrderLines; }

		function setMerchantId($merchantId) { $this->merchantId = $merchantId; }
        function getMerchantId() { return $this->merchantId; }
		
        function setMerchantOrderNo($merchantOrderNo) { $this->merchantOrderNo = $merchantOrderNo; }
        function getMerchantOrderNo() { return $this->merchantOrderNo; }
		
        function setShippingCostsInclVat($shippingCostsInclVat) { $this->shippingCostsInclVat = $shippingCostsInclVat; }
        function getShippingCostsInclVat() { return $this->shippingCostsInclVat; }
		
        function setShippingCostsVat($shippingCostsVat) { $this->shippingCostsVat = $shippingCostsVat; }
        function getShippingCostsVat() { return $this->shippingCostsVat; }
		
		function setSubTotalInclVat($subTotalInclVat) { $this->subTotalInclVat = $subTotalInclVat; }
		function getSubTotalInclVat() { return $this->subTotalInclVat; }

		function setSubTotalVat($subTotalVat) { $this->subTotalVat = $subTotalVat; }
		function getSubTotalVat() { return $this->subTotalVat; }

		function setTotalInclVat($totalInclVat) { $this->totalInclVat = $totalInclVat; }
		function getTotalInclVat() { return $this->totalInclVat; }

		function setTotalVat($totalVat) { $this->totalVat = $totalVat; }
		function getTotalVat() { return $this->totalVat; }

		function setRefundInclVat($refundInclVat) { $this->refundInclVat = $refundInclVat; }
		function getRefundInclVat() { return $this->refundInclVat; }

		function setRefundExclVat($refundExclVat) { $this->refundExclVat = $refundExclVat; }
		function getRefundExclVat() { return $this->refundExclVat; }
		
        function setExtraData($extraData) { $this->extraData = $extraData; }
        function getExtraData() { return $this->extraData; }
		
        function setShippingAddress($shippingAddress) { $this->shippingAddress = $shippingAddress; }
        function getShippingAddress() { return $this->shippingAddress; }
		
        function setStatus($status) { $this->status = $status; }
        function getStatus() { return $this->status; }
		
        function setClosedDate( $closedDate) { $this->closedDate = $closedDate; }
        function getClosedDate() { return $this->closedDate; }
		
        function setLines($lines) { $this->lines = $lines; }
        function getLines() { return $this->lines; }
		
        function setShipments($shipments) { $this->shipments = $shipments; }
        function getShipments() { return $this->shipments; }
		
        function setMaxVatRate($maxVatRate) { $this->maxVatRate = $maxVatRate; }
        function getMaxVatRate() { return $this->maxVatRate; }	

    }
}