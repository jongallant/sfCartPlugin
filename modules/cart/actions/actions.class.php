<?php

/**
 * cart actions.
 *
 * @package    bandaid
 * @subpackage cart
 * @author     Your name here
 * @version    SVN: $Id: actions.class.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class cartActions extends sfActions
{
	/**
	 * Executes index action
	 *
	 * @param sfRequest $request A request object
	 */
	public function executeIndex(sfWebRequest $request)
	{
		//get current slug
		$slug = $request->getParameter('crumb');
		$this->product = null;
		$product = null;
		$category = null;
		$this->catid = null;

		//We need to identify what path is being accessed through the URL via the slugs
		if ($slug != "") {
			$slugs = explode("/", $slug);
			$count = count($slugs);

			if ($count == 0) {					//if no slugs, set root level category (all)
				$category = Category::getRootCategory();
			}
			elseif ($count == 1) {			//if 1 slug only, set category
				$category = Category::getCategoryFromSlug($slugs[0]);
			}
			else {											//if multiple slugs
				if ($slugs[$count - 2] == "product") {	//if third to last slug is "product", we are viewing a product...
					$product = Product::getProductFromSlug($slugs[$count-1]);		//...so get that product...
					if ($count > 2) {				
						$category = Category::getCategoryFromSlug($slugs[$count-3]);  //...and its associated category...
					}
					else {
						$category = null;		//...or not, if product is being viewed directly
					}
				}
				else {
					//When viewing a product, the route will always prepend /product in order to identify.
					//If that is not found, we are just viewing a category.
					$category = Category::getCategoryFromSlug($slugs[$count-1]); 
				}
			}
		}

		if ($product != null) {
			$this->product = $product;
		}
		if ($category != null) {
			$this->catid = $category->getId();
		}

	}


	public function executeUpdate(sfWebRequest $request)
	{
		$cart = $this->getUser()->getAttribute('cart', new cartCore());

		if ($request->getParameter('cart_remove')) { $cart->del_item($request->getParameter('cart_remove')); }
		if ($request->getParameter('cart_empty')) { $cart->empty_cart(); }

		$qty = $request->getParameter('productqty');
		$id = $request->getParameter('productid');
		$name = $request->getParameter('productname');
		$price = $request->getParameter('productprice');
		$path = $request->getParameter('productpath');
		$weight = $request->getParameter('productweight');
		$url = $request->getParameter('producturl');

		if (($qty != null) and ($id != null) and ($name != null)  and ($price != null)) {
			$cartQuantity = 0;
			foreach ($cart->get_contents() as $item) {
				if ($item['id'] == $id) { $cartQuantity = $item["qty"]; break; }
			}
			$product = Doctrine::getTable('product')->createQuery('a')->where('id='.$id)->execute();
			$qtyInStock = $product[0]['quantity'];
			$truePrice = $product[0]['price'];

			$notice = "";
			if ($price != $truePrice) { $notice = "<div class='cartwarning'>An error occured. (price has been tampered)</div>"; }
			elseif ($qtyInStock < ($qty+$cartQuantity)) { $notice = "<div class='cartwarning'>Sorry, only " . $qtyInStock . " " . $name . " are left in stock.</div>"; }
			else {
				$cart->add_item($id, $qty, $price, $name, $weight, $path, $url);
				$notice = "<div class='cartmessage'>Added ".$qty." ".$name." to your cart.</div>";
			}
			 
			if ($request->isXmlHttpRequest()) {
				cart::display_cart($notice);
				return true;
			}
			else {
				$this->getUser()->setFlash('notice', sprintf($notice));
			}

		}
		$this->redirect($request->getReferer());

	}

	public function executeCheckout(sfWebRequest $request)
	{
		$cart = $this->getUser()->getAttribute('cart', new cartCore());

		if($request->getParameter('agreebutton')) { $cart->agreed = true; }
		if($request->getParameter('agree')) { $cart->agreed = true; }
		if ($request->getParameter('disagree')) { $cart->agreed = false; exit;}

		if($request->getParameter('quoteselection')) {
			$cart->setSelectedQuoteAmount($request->getParameter('quoteselection'));
			cart::show_summary();
			exit;
		}

		if ($request->getParameter('shipselect')) {
			if ($request->getParameter('shipquotes')) {$cart->setSelectedQuoteAmount($request->getParameter('shipquotes')); }
		}

		if ($request->getParameter('couponcode')) {
			$submittedCode = $request->getParameter('couponcode');
			$coupon = Doctrine_Query::create()->select('*')->from('coupon c')->where("c.code = ?", $submittedCode)->andWhere('active=1')->execute();
			if (count($coupon) != 0) {
				if ($coupon[0]['minprice'] < $cart->getSubTotal()) {
					$cart->apply_coupon($coupon[0]);
				}
				else {
					$cart->clear_coupon();
					$this->getUser()->setFlash('notice', sprintf("Coupon requires minimum purchase of $" . $coupon[0]['minprice']));
				}
			}
			else {
				$cart->clear_coupon();
				$this->getUser()->setFlash('error', sprintf("Invalid coupon code."));
			}
		}

		if ($request->getParameter('update_location')) {
			if ($request->getParameter('zipcode')) {
				$err = $cart->processZipCode($request->getParameter('zipcode'));
				if ($err!="") { $_SESSION['zipcode_error'] = $err; }
			}
		}

		else if ($request->getParameter('update_cart')) {
			$qty = $request->getParameter('itemqty');
			$id = $request->getParameter('itemid');

			if (($qty != null) and ($id != null)){
				$product = Doctrine::getTable('product')->createQuery('a')->where('id='.$id)->execute();

				///////////////////SECTION NOT WORKING
				//TODO: Cart quantities update properly, but there are no errors when requesting more than available stock.
				$qtyInStock = $product[0]['quantity'];
				if ($qtyInStock < $qty) {  $cart->qtyerror = "Sorry, there are only " . $qtyInStock . " " . $product[0]['name'] . " left in stock.";}
				else {
					$cart->update_item($id, $qty);
				}
			}
		}

		else if ( $request->getParameter('paypalcheckout')) {
			if (!$cart->agreed) { $cart->termserror = "You must agree to the terms and conditions."; }
			else {
				$paypal_count = 1;   //paypal count starts at 1
				$items_query_string;
				
				foreach ($cart->get_contents() as $item)
				{
					$items_query_string .= '&item_name_' . $paypal_count . '=' . $item['name'];
					$items_query_string .= '&amount_' . $paypal_count . '=' . $item['price'];
					$items_query_string .= '&quantity_' . $paypal_count . '=' . $item['qty'];
					++$paypal_count;
				}

				$cart->empty_cart();

				$paypalId = "yourPaylPalMerchantEmail@address.com";
				$this->redirect('https://www.paypal.com/cgi-bin/webscr?cmd=_cart&upload=1&business=' . $paypalId . $items_query_string);
			}
		}
	}



}
