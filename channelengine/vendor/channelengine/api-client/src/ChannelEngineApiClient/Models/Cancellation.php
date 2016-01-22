<?php
namespace ChannelEngineApiClient\Models {
	
	use ChannelEngineApiClient\Models\BaseModel;
	use ChannelEngineApiClient\Helpers\Collection;

    class Cancellation extends BaseModel {
		
		public static $typeMap = array(
			'lines' => 'ChannelEngineApiClient\Helpers\Collection(ChannelEngineApiClient\Models\CancellationLine)',
		);
		
		protected $orderId;
		protected $channelOrderNo;
		protected $lines;
		protected $cancellationStatus;
		protected $refundInclVat;
		protected $refundExclVat;
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	

			$this->lines = new Collection('ChannelEngineApiClient\Models\CancellationLine');
		}
		
		function setOrderId($orderId) { $this->orderId = $orderId; }
		function getOrderId() { return $this->orderId; }
		
		function setChannelOrderNo($channelOrderNo) { $this->channelOrderNo = $channelOrderNo; }
		function getChannelOrderNo() { return $this->channelOrderNo; }
		
		function setLines($lines) { $this->lines = $lines; }
		function getLines() { return $this->lines; }
		
		function setCancellationStatus($cancellationStatus) { $this->cancellationStatus = $cancellationStatus; }
		function getCancellationStatus() { return $this->cancellationStatus; }
		
		function setRefundInclVat($refundInclVat) { $this->refundInclVat = $refundInclVat; }
		function getRefundInclVat() { return $this->refundInclVat; }
		
		function setRefundExclVat($refundExclVat) { $this->refundExclVat = $refundExclVat; }
		function getRefundExclVat() { return $this->refundExclVat; }
    }
}
