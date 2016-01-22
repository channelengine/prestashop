<?php

namespace ChannelEngineApiClient\Models {

	use ChannelEngineApiClient\Models\BaseModel;
	use ChannelEngineApiClient\Enums\ShipmentLineStatus;
	
	use DateTime;
	
    class ShipmentLine extends BaseModel {
		
		public static $typeMap = array(	
		);
		
		protected $shipmentId;
		protected $orderLineId;
		protected $status;
		protected $quantity;
		protected $expectedDate;
		protected $refundInclVat;
		protected $refundExclVat;
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);
			$this->shipmentId = null;
			$this->orderLineId = 0;
			$this->status = ShipmentLineStatus::SHIPPED;
			$this->quantity = 0;
			$this->expectedDate = null;
			$this->refundInclVat = 0;
			$this->refundExclVat = 0;
		}
		
		function setShipmentId($shipmentId) { $this->shipmentId = $shipmentId; }
		function getShipmentId() { return $this->shipmentId; }
		
		function setOrderLineId($orderLineId) { $this->orderLineId = $orderLineId; }
		function getOrderLineId() { return $this->orderLineId; }
		
		function setStatus($status) { $this->status = $status; }
		function getStatus() { return $this->status; }
		
		function setQuantity($quantity) { $this->quantity = $quantity; }
		function getQuantity() { return $this->quantity; }
		
		function setExpectedDate( $expectedDate) { $this->expectedDate = $expectedDate; }
		function getExpectedDate() { return $this->expectedDate; }
		
		function setRefundInclVat($refundInclVat) { $this->refundInclVat = $refundInclVat; }
		function getRefundInclVat() { return $this->refundInclVat; }
		
		function setRefundExclVat($refundExclVat) { $this->refundExclVat = $refundExclVat; }
		function getRefundExclVat() { return $this->refundExclVat; }
    }
}