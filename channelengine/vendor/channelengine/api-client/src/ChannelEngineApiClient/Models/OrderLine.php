<?php

use ChannelEngineApiClient\Models\BaseModel;
use ChannelEngineApiClient\Models\Address;

namespace ChannelEngineApiClient\Models {

    class OrderLine extends BaseModel {
    	
		public static $typeMap = array(
		);
		
		protected $channelProductNo;
		protected $merchantProductNo;
		protected $productEan;
		protected $productName;
		protected $productBrand;
		protected $productVariantColor;
		protected $productVariantSize;
		protected $quantity;
		protected $unitPriceInclVat;
		protected $lineTotalInclVat;
		protected $vatRate;
		protected $unitVat;
		protected $lineVat;
		protected $feeFixed;
		protected $feeRate;
		protected $deliveryTimeIndication; 
		protected $expectedDeliveryDate;
		protected $status;
		protected $closedDate;
		protected $closedQuantity;
		protected $cancelledQuantity;
		protected $shippedQuantity;
		protected $mancoQuantity;
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	
		}
		
		function setChannelProductNo($channelProductNo) { $this->channelProductNo = $channelProductNo; }
		function getChannelProductNo() { return $this->channelProductNo; }
		
		function setMerchantProductNo($merchantProductNo) { $this->merchantProductNo = $merchantProductNo; }
		function getMerchantProductNo() { return $this->merchantProductNo; }
		
		function setProductEan($productEan) { $this->productEan = $productEan; }
		function getProductEan() { return $this->productEan; }
		
		function setProductName($productName) { $this->productName = $productName; }
		function getProductName() { return $this->productName; }
		
		function setProductBrand($productBrand) { $this->productBrand = $productBrand; }
		function getProductBrand() { return $this->productBrand; }
		
		function setProductVariantColor($productVariantColor) { $this->productVariantColor = $productVariantColor; }
		function getProductVariantColor() { return $this->productVariantColor; }
		
		function setProductVariantSize($productVariantSize) { $this->productVariantSize = $productVariantSize; }
		function getProductVariantSize() { return $this->productVariantSize; }
		
		function setQuantity($quantity) { $this->quantity = $quantity; }
		function getQuantity() { return $this->quantity; }
		
		function setUnitPriceInclVat($unitPriceInclVat) { $this->unitPriceInclVat = $unitPriceInclVat; }
		function getUnitPriceInclVat() { return $this->unitPriceInclVat; }
		
		function setLineTotalInclVat($lineTotalInclVat) { $this->lineTotalInclVat = $lineTotalInclVat; }
		function getLineTotalInclVat() { return $this->lineTotalInclVat; }
		
		function setVatRate($vatRate) { $this->vatRate = $vatRate; }
		function getVatRate() { return $this->vatRate; }
		
		function setUnitVat($unitVat) { $this->unitVat = $unitVat; }
		function getUnitVat() { return $this->unitVat; }
		
		function setLineVat($lineVat) { $this->lineVat = $lineVat; }
		function getLineVat() { return $this->lineVat; }
		
		function setFeeFixed($feeFixed) { $this->feeFixed = $feeFixed; }
		function getFeeFixed() { return $this->feeFixed; }
		
		function setFeeRate($feeRate) { $this->feeRate = $feeRate; }
		function getFeeRate() { return $this->feeRate; }
		
		function setExpectedDeliveryDate( $expectedDeliveryDate) { $this->expectedDeliveryDate = $expectedDeliveryDate; }
		function getExpectedDeliveryDate() { return $this->expectedDeliveryDate; }
		
		function setDeliveryTimeIndication($deliveryTimeIndication) { $this->deliveryTimeIndication = $deliveryTimeIndication; }
		function getDeliveryTimeIndication() { return $this->deliveryTimeIndication; }
		  
		function setStatus($status) { $this->status = $status; }
		function getStatus() { return $this->status; }
		
		function setClosedDate( $closedDate) { $this->closedDate = $closedDate; }
		function getClosedDate() { return $this->closedDate; }
		
		function setClosedQuantity($closedQuantity) { $this->closedQuantity = $closedQuantity; }
		function getClosedQuantity() { return $this->closedQuantity; }
		
		function setCancelledQuantity($cancelledQuantity) { $this->cancelledQuantity = $cancelledQuantity; }
		function getCancelledQuantity() { return $this->cancelledQuantity; }
		
		function setShippedQuantity($shippedQuantity) { $this->shippedQuantity = $shippedQuantity; }
		function getShippedQuantity() { return $this->shippedQuantity; }
		
		function setMancoQuantity($mancoQuantity) { $this->mancoQuantity = $mancoQuantity; }
		function getMancoQuantity() { return $this->mancoQuantity; }
        /*
        [Required]
        [StringLength(50)]
        public string ChannelProductNo { get; set; }
        // This one or ProductEan is required:
        [StringLength(50)]
        public string MerchantProductNo { get; set; }
        [StringLength(20)]
        public string ProductEan { get; set; }

        // For non-vital fields, no string length errors, but they are silently truncated.
        // productName, (100)
        // productGroup, (50)
        // productBrand,productVariantColor,productVariantSize (20)

        [Required]
        public string ProductName { get; set; }
        public string ProductGroup { get; set; }
        public string ProductBrand { get; set; }
        public string ProductVariantColor { get; set; }
        public string ProductVariantSize { get; set; }

        public int Quantity { get; set; }
        public decimal UnitPriceInclVat { get; set; }
        public decimal? LineTotalInclVat { get; set; }
        public decimal VatRate { get; set; }
        public decimal? UnitVat { get; set; }
        public decimal? LineVat { get; set; }
        public decimal FeeFixed { get; set; }
        public decimal FeeRate { get; set; }

        public DateTime ExpectedDeliveryDate { get; set; }

        public OrderStatus Status { get; set; }

        public DateTime? ClosedDate { get; set; }
        public int ClosedQuantity { get; set; }
		public int CancelledQuantity { get; set; }
		public int ShippedQuantity { get; set; }
        public int MancoQuantity { get; set; }
        */
    }
}