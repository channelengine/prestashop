<?php
namespace ChannelEngineApiClient\Models {
	
	use ChannelEngineApiClient\Models\BaseModel;
use ChannelEngineApiClient\Enums\Gender;
		
    class Address extends BaseModel {
		
		public static $typeMap = array(
			
		);
		
		// For non-vital fields, no string length errors, but they are silently truncated.
        // salutation, companyName, firstName, lastName, streetName, city: 50 characters
		
		protected $gender; // https://en.wikipedia.org/wiki/ISO/IEC_5218
		protected $salutation;
		protected $companyName;
		protected $firstName;
		protected $lastName;
		protected $streetName;
		protected $houseNr;
		protected $houseNrAddition;
		protected $zipCode;
		protected $city;
		protected $countryIso;
		
		public function __construct()
		{
			self::$typeMap = array_merge(parent::$typeMap, self::$typeMap);	
			
			$this->gender = Gender::NOT_APPLICABLE;
			$this->salutation = '';
			$this->companyName = '';
			$this->firstName = '';
			$this->lastName = '';
			$this->streetName = '';
			$this->houseNr = '';
			$this->houseNrAddition = '';
			$this->zipCode = '';
			$this->city = '';
			$this->countryIso = '';
		}
		
		function setGender($gender) { $this->gender = $gender; }
		function getGender() { return $this->gender; }
		
		function setSalutation($salutation) { $this->salutation = $salutation; }
		function getSalutation() { return $this->salutation; }
		
		function setCompanyName($companyName) { $this->companyName = $companyName; }
		function getCompanyName() { return $this->companyName; }
		
		function setFirstName($firstName) { $this->firstName = $firstName; }
		function getFirstName() { return $this->firstName; }
		
		function setLastName($lastName) { $this->lastName = $lastName; }
		function getLastName() { return $this->lastName; }
		
		function setStreetName($streetName) { $this->streetName = $streetName; }
		function getStreetName() { return $this->streetName; }
	
		function setHouseNr($houseNr) {
			
			if(strlen($houseNr) > 50)  {
				throw new Exception('HouseNr may not exceed 50 characters');
			}
			
			$this->houseNr = $houseNr;
		}
		function getHouseNr() { return $this->houseNr; }
		
		function setHouseNrAddition($houseNrAddition) {
			
			if(strlen($houseNrAddition) > 50)  {
				throw new Exception('HouseNrAddition may not exceed 50 characters');
			}
			
			$this->houseNrAddition = $houseNrAddition;
			
		}
		function getHouseNrAddition() { return $this->houseNrAddition; }
		
		function setZipCode($zipCode) { $this->zipCode = $zipCode; }
		function getZipCode() { return $this->zipCode; }
		
		function setCity($city) { $this->city = $city; }
		function getCity() { return $this->city; }
		
		function setCountryIso($countryIso) {
			
			if(strlen($countryIso) > 2)  {
				throw new Exception('CountryIso may not exceed 2 characters');
			}
			
			$this->countryIso = $countryIso;
			
		}
		function getCountryIso() { return $this->countryIso; }

    }
}
?>