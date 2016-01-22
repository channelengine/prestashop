<?php

use ChannelEngineApiClient\Models\BaseModel;
use ChannelEngineApiClient\Models\Address;

namespace ChannelEngineApiClient\Models {

    class ReturnLine extends BaseModel {
		
		public static $typeMap = array(		
		);
		
		protected $returnId; 
		protected $orderLineId; 
		protected $shipmentLineId; 
		protected $quantity; 
		protected $acceptedQuantity; 
		protected $rejectedQuantity; 
		protected $refundInclVat; 
		protected $refundExclVat; 
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	
		}
		
		function setReturnId($returnId) { $this->returnId = $returnId; }
		function getReturnId() { return $this->returnId; }

		function setOrderLineId($orderLineId) { $this->orderLineId = $orderLineId; }
		function getOrderLineId() { return $this->orderLineId; }

		function setShipmentLineId($shipmentLineId) { $this->shipmentLineId = $shipmentLineId; }
		function getShipmentLineId() { return $this->shipmentLineId; }

		function setQuantity($quantity) { $this->quantity = $quantity; }
		function getQuantity() { return $this->quantity; }

		function setAcceptedQuantity($acceptedQuantity) { $this->acceptedQuantity = $acceptedQuantity; }
		function getAcceptedQuantity() { return $this->acceptedQuantity; }

		function setRejectedQuantity($rejectedQuantity) { $this->rejectedQuantity = $rejectedQuantity; }
		function getRejectedQuantity() { return $this->rejectedQuantity; }

		function setRefundInclVat($refundInclVat) { $this->refundInclVat = $refundInclVat; }
		function getRefundInclVat() { return $this->refundInclVat; }

		function setRefundExclVat($refundExclVat) { $this->refundExclVat = $refundExclVat; }
		function getRefundExclVat() { return $this->refundExclVat; }

    }
}
