<?php

use ChannelEngineApiClient\Models\BaseModel;

namespace ChannelEngineApiClient\Models {
	
	class Message extends BaseModel {
		
		public static $typeMap = array(

		);
		
		protected $message;
		protected $modelState;
		
		function setMessage($message) { $this->message = $message; }
		function getMessage() { return $this->message; }

		function setModelState($modelState) { $this->modelState = $modelState; }
		function getModelState() { return $this->modelState; }
	}
}