<?php

namespace ChannelEngineApiClient\Models {
	
	use ChannelEngineApiClient\Models\BaseModel;

    class Product extends BaseModel {
		
		public static $typeMap = array(
			'extraData' => 'ChannelEngineApiClient\Models\Collection(ChannelEngineApiClient\Models\ProductExtraDataItem)',
		);

		protected $name;
		protected $description;
		protected $brand;
		protected $size;
		protected $color;
		protected $ean; 
		protected $groupNo;
		protected $merchantProductNo;
		protected $vendorProductNo;
		protected $price;
		protected $listPrice;
		protected $purchasePrice;
		protected $vatRate;
		protected $margin;
		protected $stock;
		protected $shippingCost;
		protected $shippingTime;
		protected $url;
		protected $imageUrl;
		protected $categoryTrail;
		protected $extraData; 
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	
		}

		function setName($name) { $this->name = $name; }
		function getName() { return $this->name; }

		function setDescription($description) { $this->description = $description; }
		function getDescription() { return $this->description; }

		function setBrand($brand) { $this->brand = $brand; }
		function getBrand() { return $this->brand; }

		function setSize($size) { $this->size = $size; }
		function getSize() { return $this->size; }

		function setColor($color) { $this->color = $color; }
		function getColor() { return $this->color; }

		function setEan($ean) { $this->ean = $ean; }
		function getEan() { return $this->ean; } 

		function setGroupNo($groupNo) { $this->groupNo = $groupNo; }
		function getGroupNo() { return $this->groupNo; }

		function setMerchantProductNo($merchantProductNo) { $this->merchantProductNo = $merchantProductNo; }
		function getMerchantProductNo() { return $this->merchantProductNo; }

		function setVendorProductNo($vendorProductNo) { $this->vendorProductNo = $vendorProductNo; }
		function getVendorProductNo() { return $this->vendorProductNo; }

		function setPrice($price) { $this->price = $price; }
		function getPrice() { return $this->price; }

		function setListPrice($listPrice) { $this->listPrice = $listPrice; }
		function getListPrice() { return $this->listPrice; }

		function setPurchasePrice($purchasePrice) { $this->purchasePrice = $purchasePrice; }
		function getPurchasePrice() { return $this->purchasePrice; }

		function setVatRate($vatRate) { $this->vatRate = $vatRate; }
		function getVatRate() { return $this->vatRate; }

		function setMargin($margin) { $this->margin = $margin; }
		function getMargin() { return $this->margin; }

		function setStock($stock) { $this->stock = $stock; }
		function getStock() { return $this->stock; }

		function setShippingCost($shippingCost) { $this->shippingCost = $shippingCost; }
		function getShippingCost() { return $this->shippingCost; }

		function setShippingTime($shippingTime) { $this->shippingTime = $shippingTime; }
		function getShippingTime() { return $this->shippingTime; }

		function setUrl($url) { $this->url = $url; }
		function getUrl() { return $this->url; }

		function setImageUrl($imageUrl) { $this->imageUrl = $imageUrl; }
		function getImageUrl() { return $this->imageUrl; }

		function setCategoryTrail($categoryTrail) { $this->categoryTrail = $categoryTrail; }
		function getCategoryTrail() { return $this->categoryTrail; }

		function setExtraData($extraData) { $this->extraData = $extraData; }
		function getExtraData() { return $this->extraData; }

    }
}