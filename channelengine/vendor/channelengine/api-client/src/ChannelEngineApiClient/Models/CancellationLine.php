<?php

namespace ChannelEngineApiClient\Models {
	
	use ChannelEngineApiClient\Models\BaseModel;

    class CancellationLine extends BaseModel {
		
		public static $typeMap = array(
			
		);
		
		protected $cancellationId;
		protected $orderLineId;
		protected $quantity;
		protected $status;
		protected $refundInclVat;
		protected $refundExclVat;
		
		function setCancellationId($cancellationId) { $this->cancellationId = $cancellationId; }
		function getCancellationId() { return $this->cancellationId; }
		
		function setOrderLineId($orderLineId) { $this->orderLineId = $orderLineId; }
		function getOrderLineId() { return $this->orderLineId; }
		
		function setQuantity($quantity) { $this->quantity = $quantity; }
		function getQuantity() { return $this->quantity; }
		
		function setStatus($status) { $this->status = $status; }
		function getStatus() { return $this->status; }
		
		function setRefundInclVat($refundInclVat) { $this->refundInclVat = $refundInclVat; }
		function getRefundInclVat() { return $this->refundInclVat; }
		
		function setRefundExclVat($refundExclVat) { $this->refundExclVat = $refundExclVat; }
		function getRefundExclVat() { return $this->refundExclVat; }
		
    }
}
