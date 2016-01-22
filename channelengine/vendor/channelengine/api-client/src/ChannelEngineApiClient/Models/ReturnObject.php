<?php

namespace ChannelEngineApiClient\Models {

	use ChannelEngineApiClient\Models\BaseModel;
	use ChannelEngineApiClient\Models\Address;
	use ChannelEngineApiClient\Helpers\Collection;

    class ReturnObject extends BaseModel {
		
		public static $typeMap = array(	
			'lines' => 'ChannelEngineApiClient\Helpers\Collection(ChannelEngineApiClient\Models\ReturnLine)'
		);
		
		protected $orderId;
		protected $shipmentId;
		protected $merchantReturnNo;
    protected $createdAt;
    protected $updatedAt;
		protected $status;
		protected $reason;
		protected $comment;
		protected $merchantComment;
		protected $refundInclVat;
		protected $refundExclVat;
		protected $lines;
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	

			$this->lines = new Collection('ChannelEngineApiClient\Models\ReturnLine');
		}
		
		function setOrderId($orderId) { $this->orderId = $orderId; }
		function getOrderId() { return $this->orderId; }
  
		function setShipmentId($shipmentId) { $this->shipmentId = $shipmentId; }
		function getShipmentId() { return $this->shipmentId; }
		
		function setMerchantReturnNo($merchantReturnNo) { $this->merchantReturnNo = $merchantReturnNo; }
		function getMerchantReturnNo() { return $this->merchantReturnNo; }
		
    function setCreatedAt( $createdAt) { $this->createdAt = $createdAt; }
    function getCreatedAt() { return $this->createdAt; }
		
    function setUpdatedAt( $updatedAt) { $this->updatedAt = $updatedAt; }
    function getUpdatedAt() { return $this->updatedAt; }
		
		function setStatus($status) { $this->status = $status; }
		function getStatus() { return $this->status; }
		
		function setReason($reason) { $this->reason = $reason; }
		function getReason() { return $this->reason; }
		
		function setComment($comment) { $this->comment = $comment; }
		function getComment() { return $this->comment; }
		
		function setMerchantComment($merchantComment) { $this->merchantComment = $merchantComment; }
		function getMerchantComment() { return $this->merchantComment; }
		
		function setRefundInclVat($refundInclVat) { $this->refundInclVat = $refundInclVat; }
		function getRefundInclVat() { return $this->refundInclVat; }
		
		function setRefundExclVat($refundExclVat) { $this->refundExclVat = $refundExclVat; }
		function getRefundExclVat() { return $this->refundExclVat; }
		
		function setLines($lines) { $this->lines = $lines; }
		function getLines() { return $this->lines; }
    }
}
