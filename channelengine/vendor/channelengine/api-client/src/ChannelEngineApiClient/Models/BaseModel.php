<?php

namespace ChannelEngineApiClient\Models {
	
    abstract class BaseModel {
       
		protected $id;
		
		public static $typeMap = array(
			
		);
		
		public function getTypeName()
		{
			return get_called_class();	
		}
		
		public function getProperties()
		{
			return get_object_vars($this);	
		}
		
        function setId($id) { $this->id = $id; }
		function getId() { return $this->id; }
		
    }
}
