<?php

use ChannelEngineApiClient\Models\BaseModel;

namespace ChannelEngineApiClient\Models {

    class ImportResult extends BaseModel {
		
		public static $typeMap = array(

		);
		
		protected $submitted;
		protected $updated;
		protected $new;
		protected $unchanged;

		function setSubmitted($submitted) { $this->submitted = $submitted; }
		function getSubmitted() { return $this->submitted; }
		
		function setUpdated($updated) { $this->updated = $updated; }
		function getUpdated() { return $this->updated; }

		function setNew($new) { $this->new = $new; }
		function getNew() { return $this->new; }

		function setUnchanged($unchanged) { $this->unchanged = $unchanged; }
		function getUnchanged() { return $this->unchanged; }
    }
}
