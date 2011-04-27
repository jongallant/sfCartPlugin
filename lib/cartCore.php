<?php

class cartCore {

	//Cart Item Data
	private $items = array();
	private $itemprices = array();
	private $itemqtys = array();
	private $itemname = array();
	private $photopath = array();
	private $weights = array();
	private $urls = array();

	private $total = 0;
	private $itemcount = 0;
	private $totalWeight = 0;
	private $state = "";
	private $zipcode = "";
	private $validatedaddress = "";
	private $tax_percentage = 0;
	private $quotes = array();
	private $selectedQuoteAmount = 0;
	public $agreed = false;
	public $agreeshown = false;
	private $coupondiscount = 0;
	private $couponcode = "";

	//Properties
	public function getTotal() {
		return ($this->total*(1 + $this->tax_percentage/100)) + $this->selectedQuoteAmount - $this->coupondiscount;
	}

	public function getSubTotal() {
		return $this->total;
	}

	public function getItemCount() {
		return $this->itemcount;
	}

	public function getWeight() {
		return $this->totalWeight;
	}

	public function getLocation() {
		return $this->state;
	}

	public function getTax() {
		return $this->tax_percentage;
	}

	public function getShippingQuotes() {
		return $this->quotes;
	}

	public function getSelectedQuote() {
		return $this->selectedQuoteAmount;
	}
	public function setSelectedQuote($value) {
		$this->selectedQuoteAmount = $value;
	}

	public function getCoupon() {
		return $this->couponcode;
	}

	public function getCouponDiscount() {
		return $this->coupondiscount;
	}

	public function getZipcode() {
		return $this->zipcode;
	}

	//returns cart contents in array format
	public function get_contents() {
		$items = array();
		foreach($this->items as $tmp_item)
		{
			$item = FALSE;
			$item['id'] = $tmp_item;
			$item['qty'] = $this->itemqtys[$tmp_item];
			$item['price'] = $this->itemprices[$tmp_item];
			$item['name'] = $this->itemname[$tmp_item];
			$item['subtotal'] = $item['qty'] * $item['price'];
			$item['photo_path'] = $this->photopath[$tmp_item];
			$item['weight'] = $this->weights[$tmp_item];
			$item['url'] = $this->urls[$tmp_item];
			$items[] = $item;
		}
		return $items;
	}

	public function refresh_shipping_quotes() {
		$this->processZipCode($this->zipcode);				//US
		//$this->processPostalCode($this->zipcode);  //Canada
	}

	//Reset cart data
	private function reset_cart() {
		$this->empty_cart();
		unset($this->quotes);

		$this->totalWeight = 0;
		$this->zipcode = "";
		$this->state = "";
		$this->validatedaddress = "";
		$this->tax_percentage = 0;

		$this->selectedQuoteAmount = 0;
		$this->agreed = false;
		$this->agreeshown = false;
		$this->coupondiscount = 0;
		$this->couponcode = "";
	}

	//Validate the zip code -- process the zipcode
	public function processZipCode($code) {
		unset($this->quotes);
		$this->selectedQuoteAmount = 0;
		$err = "";
		if (!is_numeric($code) or ($code > 99999) or ($code < 1)) { $err = "Invalid Zipcode"; }
		else {
			$this->state = $this->getState($code);		//convert zip to state
			$this->updateTaxRate();										//set tax rate based on state
			$this->zipcode = $code;										
			$this->getUpsRates();											//get available shipping rates
		}
		return $err;
	}

	//get the tax rate based on the state the cart is set to
	private function updateTaxRate() {
		if ($this->state != "") {
			$rate = Doctrine_Query::create()->from('Tax t')->where('region_code=?', $this->state)->fetchOne();
			$this->tax_percentage = $rate->getRate();
		}
	}

	//converts zip into state
	private function getState($zipcode) {
		$state = "";
		if (($zipcode > 00600) && ($zipcode < 00999)) { $state = "PR"; }      //Puerto Rico PR and U.S. Virgin Islands (VI) (00600-00999)
		elseif (($zipcode > 01000) && ($zipcode < 02799)) { $state = "MA"; }  //Massachusetts MA (01000-02799)
		elseif (($zipcode > 02800) && ($zipcode < 02999)) { $state = "RI"; }  //Rhode Island RI (02800-02999)
		elseif (($zipcode > 03000) && ($zipcode < 03899)) { $state = "NH"; }  //New Hampshire NH(03000-03899)
		elseif (($zipcode > 03900) && ($zipcode < 04999)) { $state = "ME"; }  //Maine ME(03900-04999)
		elseif (($zipcode > 05000) && ($zipcode < 05999)) { $state = "VT"; }  //Vermont VT(05000-05999)
		elseif (($zipcode > 06000) && ($zipcode < 06999)) { $state = "CT"; }  //Connecticut CT(06000-06999)
		elseif (($zipcode > 07000) && ($zipcode < 08999)) { $state = "NJ"; }  //New Jersey NJ(07000-08999)
		elseif (($zipcode > 10000) && ($zipcode < 14999)) { $state = "NY"; }  //New York NY(10000-14999)
		elseif (($zipcode > 15000) && ($zipcode < 19699)) { $state = "PA"; }  //Pennsylvania PA(15000-19699)
		elseif (($zipcode > 19700) && ($zipcode < 19999)) { $state = "DE"; }  //delaware DE(19700-19999)
		elseif (($zipcode > 20000) && ($zipcode < 20599)) { $state = "DC"; }  //District of Columbia DC(20000-20599)
		elseif (($zipcode > 20600) && ($zipcode < 21999)) { $state = "MD"; }  //Maryland MD(20600-21999)
		elseif (($zipcode > 22000) && ($zipcode < 24699)) { $state = "VA"; }  //Virginia VA(22000-24699, also some taken from 20000-20599 DC <--????)
		elseif (($zipcode > 24700) && ($zipcode < 26999)) { $state = "WV"; }  //West Virginia WV(24700-26999)
		elseif (($zipcode > 27000) && ($zipcode < 28999)) { $state = "NC"; }  //North Carolina NC(27000-28999)
		elseif (($zipcode > 29000) && ($zipcode < 29999)) { $state = "SC"; }  //South Carolina SC(29000-29999)
		elseif ((($zipcode > 30000) && ($zipcode < 31999)) || ($zipcode == 39901)) { $state = "GA"; }  //Georgia GA(30000-31999, 39901[Atlanta])
		elseif (($zipcode > 32000) && ($zipcode < 34999)) { $state = "FL"; }  //Florida FL(32000-34999)
		elseif (($zipcode > 00600) && ($zipcode < 00999)) { $state = "ML"; }  //the military (34090-34095?)
		elseif (($zipcode > 35000) && ($zipcode < 36999)) { $state = "AL"; }  //Alabama AL(35000-36999)
		elseif (($zipcode > 37000) && ($zipcode < 38599)) { $state = "TN"; }  //Tennessee TN(37000-38599)
		elseif (($zipcode > 38600) && ($zipcode < 39999) && ($zipcode != 39901)) { $state = "MS"; }  //Mississippi MS(38600-39999)
		elseif (($zipcode > 40000) && ($zipcode < 42799)) { $state = "KY"; }  //Kentucky KY(40000-42799)
		elseif (($zipcode > 43000) && ($zipcode < 45999)) { $state = "OH"; }  //Ohio OH(43000-45999)
		elseif (($zipcode > 46000) && ($zipcode < 47999)) { $state = "IN"; }  //Indiana IN(46000-47999)
		elseif (($zipcode > 48000) && ($zipcode < 49999)) { $state = "MI"; }  //Michigan MI(48000-49999)
		elseif (($zipcode > 50000) && ($zipcode < 52999)) { $state = "IA"; }  //Iowa IA(50000-52999)
		elseif (($zipcode > 53000) && ($zipcode < 54999)) { $state = "WI"; }  //Wisconsin WI(53000-54999)
		elseif (($zipcode > 55000) && ($zipcode < 56999)) { $state = "MN"; }  //Minnesota MN(55000-56999)
		elseif (($zipcode > 57000) && ($zipcode < 57999)) { $state = "SD"; }  //South Dakota SD(57000-57999)
		elseif (($zipcode > 58000) && ($zipcode < 58999)) { $state = "ND"; }  //North Dakota ND(58000-58999)
		elseif (($zipcode > 59000) && ($zipcode < 59999)) { $state = "MT"; }  //Montana MT(59000-59999)
		elseif (($zipcode > 60000) && ($zipcode < 62999)) { $state = "IL"; }  //Illinois IL(60000-62999)
		elseif (($zipcode > 63000) && ($zipcode < 65999)) { $state = "MO"; }  //Missouri MO(63000-65999)
		elseif (($zipcode > 66000) && ($zipcode < 67999)) { $state = "KS"; }  //Kansas KS(66000-67999)
		elseif (($zipcode > 68000) && ($zipcode < 69999)) { $state = "NE"; }  //Nebraska NE(68000-69999)
		elseif (($zipcode > 70000) && ($zipcode < 71599)) { $state = "LA"; }  //Louisiana LA(70000-71599)
		elseif (($zipcode > 71600) && ($zipcode < 72999)) { $state = "AR"; }  //Arkansas AR(71600-72999)
		elseif (($zipcode > 73000) && ($zipcode < 74999)) { $state = "OK"; }  //Oklahoma OK(73000-74999)
		elseif (($zipcode > 75000) && ($zipcode < 79999)) { $state = "TX"; }  //Texas TX(75000-79999)
		elseif (($zipcode > 80000) && ($zipcode < 81999)) { $state = "CO"; }  //Colorado CO(80000-81999)
		elseif (($zipcode > 82000) && ($zipcode < 83199)) { $state = "WY"; }  //Wyoming WY(82000-83199)
		elseif (($zipcode > 83200) && ($zipcode < 83999)) { $state = "ID"; }  //Idaho ID(83200-83999)
		elseif (($zipcode > 84000) && ($zipcode < 84999)) { $state = "UT"; }  //Utah UT(84000-84999)
		elseif (($zipcode > 85000) && ($zipcode < 86999)) { $state = "AZ"; }  //Arizona AZ(85000-86999)
		elseif (($zipcode > 87000) && ($zipcode < 88999)) { $state = "NM"; }  //New Mexico NM(87000-88999)
		elseif (($zipcode > 89000) && ($zipcode < 89999)) { $state = "NV"; }  //Nevada NV(89000-89999)
		elseif (($zipcode > 90000) && ($zipcode < 95999)) { $state = "CA"; }  //California CA(90000-95999)
		elseif (($zipcode > 96000) && ($zipcode < 96699)) { $state = "ML"; }  //the military (96000-96699?)
		elseif (($zipcode > 96700) && ($zipcode < 96899)) { $state = "HI"; }  //Hawaii HI(96700-96899)
		elseif (($zipcode > 97000) && ($zipcode < 97999)) { $state = "OR"; }  //Oregon OR(97000-97999)
		elseif (($zipcode > 98000) && ($zipcode < 99499)) { $state = "WA"; }  //Washington WA(98000-99499)
		elseif (($zipcode > 99500) && ($zipcode < 99999)) { $state = "AK"; }  //Alaska AK(99500-99999)
		return $state;
	}

	//validate a postal code (Canada)
	private function processPostalCode($code) {
		unset($this->quotes);
		$this->selectedQuoteAmount = 0;
		$err = "";
		if(preg_match("/^([a-ceghj-npr-tv-z]){1}[0-9]{1}[a-ceghj-npr-tv-z]{1}[0-9]{1}[a-ceghj-npr-tv-z]{1}[0-9]{1}$/i",$code)) {
			$this->zipcode = $code;
			$this->getPurolatorRates();			//Get available shipping rates
		}
		else {
			$err = "Invalid Postal Code";
			$this->zipcode = "";
		}
		return $err;
	}

	private function getUpsRates() {
		$this->quotes = $this->calculate_per_item_shipping();
	}

	//Get available purolator rates
	private function getPurolatorRates() {
		$purolator = new purolator();

		//validate postal code
		$shipping = $purolator->getLocation($this->zipcode);

		if ($shipping != null) {
			$this->validatedaddress = $shipping;

			//get rates
			$purolator->updateRates($this->totalWeight, $this->itemcount);
			$this->quotes = $purolator->quotes;
			$this->selectedQuoteAmount = $purolator->calcBestRate();
		}
	}

	private function calc_package_shipping($weight) {
		$ups = new ups();
		$shipping = $ups->get_item_shipping($weight, $this->zipcode);
		return $shipping;
	}

	//UPS calculator - Divides packages into 150lbs max packages, and requests multiple quotes if needed
	//Returns a sum of all the quotes added together.
	private function calculate_per_item_shipping() {

		$currWeight = 0;
		$packageCount = 0;

		//Loop thru cart items - adding weights to packages
		foreach($this->get_contents() as $item) {
			for ($i = 0; $i < $item["qty"]; $i++) {
				$itemWeight = $item["weight"];

				if ($itemWeight > 150) {  //150lbs max
					$_SESSION['zipcode_error'] = $item['name'] . " weighs more than 150lbs.";
					return false;
				}
				else {
					$currWeight += $itemWeight;				//Add item weight to active package
					if ($currWeight > 150)  {						//Max weight reached for active package
						$currWeight -= $itemWeight;			//Remove item from current package, too heavy
						$loopPack = 0;
						$itemUsed = false;

						//Check if an existing package can take the item
						while (($loopPack != $packageCount) or ($itemUsed = false)) {
							if ($packages[$loopPack] + $itemWeight < 150) {
								$packages[$loopPack] += $itemWeight;
								$itemUsed = true;
							}
							$loopPack++;
						}

						//if the item didn't fit in an existing package, create a new package for it
						if ($itemUsed == false) {
							$packageCount++;
							$packages[$packageCount-1] = $currWeight;
							$currWeight = $item["weight"];		//Put unused item back in active package
						}
					}
				}
			}
		}
		//The remainder becomes a package
		$packageCount++;
		$packages[$packageCount-1] = $currWeight;

		for ($i = 0; $i < $packageCount; $i++) {
			$temptotal = $this->calc_package_shipping($packages[$i]);
			if ($temptotal['Ground'] != 0) { $total['Ground'] += $temptotal['Ground']; }
			if ($temptotal['3 Day Select'] != 0) { $total['3 Day Select'] += $temptotal['3 Day Select']; }
			if ($temptotal['2nd Day Air'] != 0) { $total['2nd Day Air'] += $temptotal['2nd Day Air']; }
			if ($temptotal['Next Day Air Saver'] != 0) { $total['Next Day Air Saver'] += $temptotal['Next Day Air Saver']; }
			if ($temptotal['Next Day Air Early AM'] != 0) { $total['Next Day Air Early AM'] += $temptotal['Next Day Air Early AM']; }
			if ($temptotal['Next Day Air'] != 0) { $total['Next Day Air'] += $temptotal['Next Day Air']; }
		}

		$this->selectedQuoteAmount = $total['Ground'];

		return $total;
	}

	//Add item to cart
	public function add_item($item_id, $item_qty=1, $item_price, $item_name, $item_weight, $photo_path, $item_url) {
		$valid_item_qty = $valid_item_price = false;

		if (preg_match("/^[0-9-]+$/i", $item_qty)) { $valid_item_qty = true; }
		if (is_numeric($item_price)){	$valid_item_price = true;	}

		if ($valid_item_qty !== false && $valid_item_price !== false)
		{
			//if already in cart, increment quantity
			if (isset($this->itemqtys[$item_id]) && ($this->itemqtys[$item_id] > 0))
			{
				$this->itemqtys[$item_id] = $item_qty + $this->itemqtys[$item_id];
				$this->_update_total();
			}
			//new item
			else
			{
				$this->items[] = $item_id;
				$this->itemqtys[$item_id] = $item_qty;
				$this->itemprices[$item_id] = $item_price;
				$this->itemname[$item_id] = $item_name;
				$this->photopath[$item_id] = $photo_path;
				$this->weights[$item_id] = $item_weight;
				$this->urls[$item_id] = $item_url;
			}

			unset($this->quotes);
			$this->_update_total();
				
			sfContext::getInstance()->getUser()->setAttribute('cart', $this);

			return true;
		}
		else if	($valid_item_qty !== true)
		{
			$error_type = 'qty';
			return $error_type;
		}
		else if	($valid_item_price !== true)
		{
			$error_type = 'price';
			return $error_type;
		}
		else {
			$error_type = "qty";
			return $error_type;
		}
	}

	//Update item already in cart
	public function update_item($item_id, $item_qty) {
		if (preg_match("/^[0-9-]+$/i", $item_qty))
		{
			if($item_qty < 1)	{	$this->del_item($item_id);}
			else{	$this->itemqtys[$item_id] = $item_qty;}
			unset($this->quotes);
			$this->_update_total();
			return true;
		}
		else { return false; }
	}

	//Remove item from cart
	public function del_item($item_id) 	{
		$ti = array();
		$this->itemqtys[$item_id] = 0;
		foreach($this->items as $item) {
			if($item != $item_id)	{	$ti[] = $item;}
		}
		$this->items = $ti;
		unset($this->quotes);
		$this->_update_total();
	}

	//Remove all cart items
	public function empty_cart() {
		$this->total = 0;
		$this->itemcount = 0;
		$this->items = array();
		$this->itemprices = array();
		$this->itemqtys = array();
		$this->itemname = array();
		$this->photopath = array();
		$this->weights = array();
		$this->urls = array();
	}

	//Update the cart totals
	private function _update_total()	{
		$this->itemcount = 0;
		$this->total = 0;
		$this->totalWeight = 0;
		if(sizeof($this->items > 0))
		{
			foreach($this->items as $item)
			{
				$this->total = $this->total + ($this->itemprices[$item] * $this->itemqtys[$item]);
				$this->totalWeight = $this->totalWeight + $this->weights[$item];
				$this->itemcount += $this->itemqtys[$item];
			}
		}
	}

	//apply discount
	public function apply_coupon($coupon) {
		$this->couponcode = $coupon['code'];
		$this->coupondiscount = $coupon['discount'];
	}

	//remove discount
	public function clear_coupon() {
		$this->coupondiscount = 0;
		$this->couponcode = "";
	}

}
?>
