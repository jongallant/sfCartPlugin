<?php
class ups {

	function getMethod() {
		$method= array(
				'1DA'    => 'Next Day Air',
				'2DA'    => '2nd Day Air',
				'3DS'    => '3 Day Select',
				'GND'    => 'Ground',
		);
		return $method;
	}

	function get_item_shipping($weight, $zipcode)
	{
		unset($_SESSION['zipcode_error']);
		if($weight > 150) {
			$_SESSION['zipcode_error'] = "A cart item requires freight.";
		}
		if (!isset($_SESSION['zipcode_error'])) {
			$services = $this->getMethod();
			$ch = curl_init();
			foreach ($services as $key => $service) {
				$Url = join("&", array("http://www.ups.com/using/services/rave/qcostcgi.cgi?accept_UPS_license_agreement=yes",
				"10_action=3",
				"13_product=".$key,
				"14_origCountry=US",
				"15_origPostal=90210",
				"19_destPostal=" . $zipcode,
				"22_destCountry=US",
				"23_weight=" . $weight,
				"47_rate_chart=Regular+Daily+Pickup",
				"48_container=00",
				"49_residential=2",
				"billToUPS=no")
				);

				curl_setopt($ch, CURLOPT_URL, $Url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$Results[]=curl_exec($ch);
			}
			curl_close($ch);
			$pre = "";
			$shipping_list = array();
			foreach($Results as $result) {
				$result = explode("%", $result);
				if ($services[$result[1]] != ''){
					if ((($result[1]=='XPR') && ($pre == 'XPR')) || (($result[1]=='XDM') && ($pre == 'XDM')) || (($result[1]=='1DP') && ($pre == '1DP')) || (($result[1]=='1DM') && ($pre == '1DM')) || (($result[1]=='1DA') && ($pre == '1DA')) || (($result[1]=='2DA') && ($pre == '2DA')))
					$shipping_list += array($services[$result[1]."L"] => $result[8]);
					else if (($result[1]=='GND') && ($pre == 'GND'))
					$shipping_list += array($services[$result[1]."RES"] => $result[8]);
					else
					$shipping_list += array($services[$result[1]] => $result[8]);
					$pre = $result[1];
				}
			}
		}
		$shipping_list = array_reverse($shipping_list);
		return $shipping_list;
	}

}

?>