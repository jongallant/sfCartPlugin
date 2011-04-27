<?php
//PRODUCTION
define("PRODUCTION_KEY", "xxxxxxxxxxxxxxxxxxxxxxxxxxxxx");
define("PRODUCTION_PASS", "xxxxxxx]");
define("BILLING_ACCOUNT", "xxxxxx");
define("REGISTERED_ACCOUNT", "xxxxxx");

class purolator{

	var $postalcode, $city, $province, $totalWeight, $itemCount, $quotes, $selectedQuote;

	function createAvailabilityClient() {
		$client = new SoapClient( "ServiceAvailabilityService.wsdl",
		array	(
        'trace'			=>	true,
				'location'	=>	"https://webservices.purolator.com/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx",
		#'location'	=>	"https://devwebservices.purolator.com/PWS/V1/ServiceAvailability/ServiceAvailabilityService.asmx",
        'uri'				=>	"http://purolator.com/pws/datatypes/v1",
        'login'			=>	PRODUCTION_KEY,
        'password'	=>	PRODUCTION_PASS
		)
		);
		$headers[] = new SoapHeader ( 'http://purolator.com/pws/datatypes/v1',
      'RequestContext', 
		array (
              'Version'           =>  '1.0',
              'Language'          =>  'en',
              'GroupID'           =>  'xxx',
              'RequestReference'  =>  'Rating Example'
              )
              );
              $client->__setSoapHeaders($headers);
              return $client;
	}

	function createRatesClient(){

		$client = new SoapClient( "EstimatingService.wsdl",
		array	(
				'trace'			=>	true,
        'location'	=>	"https://webservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx",
		#'location'	=>	"https://devwebservices.purolator.com/PWS/V1/Estimating/EstimatingService.asmx",
        'uri'				=>	"http://purolator.com/pws/datatypes/v1",
        'login'			=>	PRODUCTION_KEY,
        'password'	=>	PRODUCTION_PASS
		)
		);
		$headers[] = new SoapHeader ( 'http://purolator.com/pws/datatypes/v1',
        'RequestContext', 
		array (
                'Version'           =>  '1.0',
                'Language'          =>  'en',
                'GroupID'           =>  'xxx',
                'RequestReference'  =>  'Rating Example'
                )
                );
                $client->__setSoapHeaders($headers);
                return $client;
	}

	function getLocation($postalCode) 	{
		$postalCode = str_replace("-","",$postalCode);
		$postalCode = str_replace(" ","",$postalCode);

		$client = $this->createAvailabilityClient();

		$request->Addresses->ShortAddress->Country = "CA";
		$request->Addresses->ShortAddress->PostalCode = $postalCode;

		if ($this->postalcode != $postalCode) {
			$response = $client->ValidateCityPostalCodeZip($request);
			$this->postalcode = $postalCode;
			$this->city = $response->SuggestedAddresses->SuggestedAddress->Address->City;
			$this->province = $response->SuggestedAddresses->SuggestedAddress->Address->Province;
		}
		if (($this->city != "") and ($this->province != "")) { return $this->city . ", " . $this->province; }
		else { return null; }
	}

	function updateRates($cartWeight, $cartItemCount){

		$this->totalWeight = $cartWeight;
		$this->itemCount = $cartItemCount;

		$client = $this->createRatesClient();

		$request->Shipment->SenderInformation->Address->Name = "";
		$request->Shipment->SenderInformation->Address->StreetNumber = "";
		$request->Shipment->SenderInformation->Address->StreetName = "";
		$request->Shipment->SenderInformation->Address->City = "";
		$request->Shipment->SenderInformation->Address->Province = "";
		$request->Shipment->SenderInformation->Address->Country = "CA";
		$request->Shipment->SenderInformation->Address->PostalCode = "";
		$request->Shipment->SenderInformation->Address->PhoneNumber->CountryCode = "1";
		$request->Shipment->SenderInformation->Address->PhoneNumber->AreaCode = "";
		$request->Shipment->SenderInformation->Address->PhoneNumber->Phone = "";

		//$request->Shipment->ReceiverInformation->Address->Name = "";
		//$request->Shipment->ReceiverInformation->Address->StreetNumber = "";
		//$request->Shipment->ReceiverInformation->Address->StreetName = "";
		$request->Shipment->ReceiverInformation->Address->City = $this->city;
		$request->Shipment->ReceiverInformation->Address->Province = $this->province;
		$request->Shipment->ReceiverInformation->Address->Country = "CA";
		$request->Shipment->ReceiverInformation->Address->PostalCode = $this->postalcode;
		$request->Shipment->ReceiverInformation->Address->PhoneNumber->CountryCode = "1";
		//$request->Shipment->ReceiverInformation->Address->PhoneNumber->AreaCode = "";
		//$request->Shipment->ReceiverInformation->Address->PhoneNumber->Phone = "";

		$request->Shipment->PackageInformation->TotalWeight->Value = $cartWeight;
		$request->Shipment->PackageInformation->TotalWeight->WeightUnit = "lb";
		$request->Shipment->PackageInformation->TotalPieces = $cartItemCount;
		$request->Shipment->PackageInformation->ServiceID = "PurolatorExpress";

		$request->Shipment->PaymentInformation->PaymentType = "Sender";
		$request->Shipment->PaymentInformation->BillingAccountNumber = BILLING_ACCOUNT;
		$request->Shipment->PaymentInformation->RegisteredAccountNumber = REGISTERED_ACCOUNT;

		//$request->Shipment->PickupInformation->PickupType = "DropOff";
		$request->ShowAlternativeServicesIndicator = "true";
		//$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->ID = "ResidentialSignatureDomestic";
		//$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->Value = "true";
		//$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->ID = "ResidentialSignatureIntl";
		//$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->Value = "true";
		$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->ID = "OriginSignatureNotRequired";
		$request->Shipment->PackageInformation->OptionsInformation->Options->OptionIDValuePair->Value = "true";

		$response = $client->GetFullEstimate($request);

		if($response && $response->ShipmentEstimates->ShipmentEstimate)
		{
			unset($this->quotes);
			$i = 0;
			foreach($response->ShipmentEstimates->ShipmentEstimate as $estimate)
			{
				if ($estimate->ServiceID != "PurolatorGroundRegional")
				{
					//Get quote price
					$this->quotes[$i][1] = $estimate->TotalPrice;

					//Get Quote Name
					if ($estimate->ServiceID == "PurolatorExpress") { $this->quotes[$i][0] = "Purolator Express"; }
					else if ($estimate->ServiceID == "PurolatorExpress10:30AM") { $this->quotes[$i][0] = "Purolator Express 10:30AM";}
					else if ($estimate->ServiceID == "PurolatorExpress9AM") { $this->quotes[$i][0] = "Purolator Express 9AM";}
					else if ($estimate->ServiceID == "PurolatorGround") { $this->quotes[$i][0] = "Purolator Ground";}
					else if ($estimate->ServiceID == "PurolatorGround10:30AM") { $this->quotes[$i][0] = "Purolator Ground 10:30AM";}
					else if ($estimate->ServiceID == "PurolatorGround9AM") { $this->quotes[$i][0] = "Purolator Ground 9AM";}
					else { $this->quotes[$i][0] = $estimate->ServiceID;}
					$i++;
				}
			}
			sort($this->quotes);
		}
	}

	function getSelectedQuote(){ return $this->selectedQuote; }

	function calcBestRate() {
		$this->selectedQuote = 0;
		if (sizeof($this->quotes) > 0) {
			if ($this->selectedQuote == 0) { $this->selectedQuote = $this->quotes[sizeof($this->quotes)-1][1]; } //list sorted, 1st one is best
			for ($i=sizeof($this->quotes)-1; $i>=0; $i--){                                                        //loop just to make sure
				if ($this->selectedQuote > $this->quotes[$i][1]) { $this->selectedQuote = $this->quotes[$i][1]; }
			}
		}
		return $this->selectedQuote;
	}

}



?>