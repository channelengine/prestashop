<?php

namespace ChannelEngineApiClient\Helpers {
		
    class Collection extends \ArrayObject{
		
		private $type;
		private $test;
		
		
		
		public function __construct($type = 'stdObject')
		{			
			$matches = array();
			if(preg_match('/Collection\(([\\\\\w]+)\)$/', $type, $matches))
			{
				$this->type = $matches[1];
			}
			else
			{
				$this->type = $type;	
			}
			
			$this->test = new $this->type();			
		}
		
		public function append($item)
		{
			$this->checkType($item);
			parent::append($item);
		}
		
		public function offsetSet($idx, $item)
		{
			$this->checkType($item);
			parent::offsetSet($idx, $item);
		}
		
		private function checkType($item)
		{
			if(!($item instanceof $this->test))
			{
				throw new \Exception('Invalid type inserted in list with type ' . $this->type);	
			}	
		}
		
		public function getTypeName()
		{
			return $this->type;	
		}
		
	}
	
}