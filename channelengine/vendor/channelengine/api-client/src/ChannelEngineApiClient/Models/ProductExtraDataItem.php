<?php

use ChannelEngineApiClient\Models\BaseModel;

namespace ChannelEngineApiClient\Models {

    class ProductExtraDataItem extends BaseModel{
		
		public static $typeMap = array(

		);
		
		protected $key;
		protected $value;
		protected $isPublic;

		function setKey($key) { $this->key = $key; }
		function getKey() { return $this->key; }
		
		function setValue($value) { $this->value = $value; }
		function getValue() { return $this->value; }

		function setIsPublic($isPublic) { $this->isPublic = $isPublic; }
		function getIsPublic() { return $this->isPublic; }
    }
}
