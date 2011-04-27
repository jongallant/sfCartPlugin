<div id="checkoutcontainer">
	<div id='checkout'>
		<p style="font-weight:bold;margin-bottom: 10px;">Please review your order:</p>
		<div class="productcart">	
		<div id="shoppingcartheader">
		<span id='shoppingcartimg'></span>
		<span id='shoppingcartname'>Product</span>
		<span id='shoppingcartqty'>Quantity</span>
		<span id='shoppingcartprice'>Price</span>
		<span id='shoppingcartremove'></span>
		</div>

		<?php foreach($cart->get_contents() as $item) { ?>     
			<form method='post' action='/checkout'>
			<fieldset>
			<div id="shoppingcartrow">
			<span id='shoppingcartimg'> </span>
			<span id='shoppingcartname'><a href='<?php echo $item['url']?>'><?php echo $item['name'] ?></a></span>
			<span id='shoppingcartqty'>
      <!--Should have a qty error flash-->
			<input type='text' size='2' id='cart_item_id_<?php echo $item["id"]?>' name='itemqty' value='<?php echo $item["qty"]?>' />
			<input type='submit' name='update_cart' value='Update' class='cart-button' />
			</span>
			<span id='shoppingcartprice'>&#036;<?php echo $item['price']?></span>
			<span id='shoppingcartremove'>
			<a class='cart-remove' href='?cart_remove=<?php echo $item['id']?>'>Remove</a>
			</span>
			<input type='hidden' name='itemname' value='<?php echo $item['name']?>' />
			<input type='hidden' name='itemid' value='<?php echo $item['id']?>' />
			<div class="clear"></div>
			</div>
			</fieldset>
			</form>
			<?php } ?>
		</div>
	</div>

	<form method='post' action='/checkout' id='couponcode'>
		<div class='couponcoderow'>
			<?php if (isset($_SESSION['coupon_error'])) { echo '<div class="warning">Error: '.$_SESSION['coupon_error'].'</div>'; unset($_SESSION['coupon_error']); } ?>
			<label>Coupon Code:</label>
			<input type="textbox" name="couponcode" size="8" />
			<input type='submit' src='/checkout' name='couponselect' value='Apply Coupon' />
			<div style='clear:both'></div>
		</div>
 </form>
 
<?php if ((count($cart->getShippingQuotes()) > 0) and ($cart->getZipcode() != "") and ($cart->getSubtotal() > 0)) {
			$unchecked = true;
			echo "<form method='post' action='/checkout' id='shippingquotes'>";
			echo "<span>PLEASE SELECT A SHIPPING METHOD</span>";
			echo "<div style='clear:both'></div>";

	    $quotes = $cart->getShippingQuotes();
	    
	    foreach ($quotes as $key => $quote) {
				echo "<div class='shippingquoterow'>";
				echo "<label>UPS ".$key."</label>";
				if (($quote == $cart->getSelectedQuote()) and ($unchecked)) { echo "<input type='radio' checked='checked' class='quoteradio' name='shipquotes' value='".$quote."' />"; $unchecked = false; }
				else { echo "<input type='radio' class='quoteradio' name='shipquotes' value='".$quote."' />"; }
				echo "<div class='quoteamount'>&#36;".number_format($quote,2)."</div>";
				echo "<div style='clear:both'></div>";
				echo "</div>";
				next($quotes);
			}
       
			echo "<input type='submit' src='/crs/checkout' id='shippingselect' name='shipselect' value='Save Changes' class='save'/>";
			echo "<div style='clear:both'></div>";
			echo "</form>";
		} ?>
			
			<div id="zipcodecheck"> 
			<form method='post' action='/checkout' name="zipcodecheck" id="codecheck">
			<span style="font-weight: bold;">Shipping Estimate:</span>
			<input type='text' style='color:#999' onclick='if (this.value=="Zipcode") {this.value="";this.style.color="#000";}' onblur='if (this.value=="") {this.style.color="#999"; this.value="Zipcode"; }' value='Zipcode' size='10' name='zipcode' id='zipcode' />						
			<input type='hidden' name='update_location' value='true' />
			<input type='submit' name='submit_zipcode' value='Calculate Shipping' />
			</form>
			<?php if (isset($_SESSION['zipcode_error'])) { echo '<div class="warning">Error: '.$_SESSION['zipcode_error'].'</div>'; unset($_SESSION['zipcode_error']); }
			else { 
				if ((count($cart->getShippingQuotes())) > 0 and ($cart->getZipcode()) != "") { echo "<p style='margin-left: 110px;font-weight: bold; display:inline;'>Current shipping location: <span style='font-weight: normal'>".$cart->getZipcode()." (". $cart->getLocation() .")</span></p>"; } 
				else { echo "<p style='font-weight: bold;color:#ff4100;display:inline;'><img src='/sfCartPlugin/images/arrow.gif' alt='arrow' style='padding-right: 20px;' />To continue, enter your zipcode</p>"; }
			} 
			echo "</div>";
			echo "<div style='clear:both'></div>";
      
			cart::display_summary(); 
			
			//checkout form
			if (($cart->getZipcode() != "") and ($cart->getSelectedQuote() != 0) and (count($cart->getShippingQuotes()) > 0)) { ?>
			<form method='post' action='/checkout'>
      
			<?php if ($cart->agreeshown == false) { ?>
				<input type="hidden" id="agreeshown" name="agreeshown" value="no">				  
				<?php $cart->agreeshown = true;
			} else { ?>
				<input type="hidden" id="agreeshown" name="agreeshown" value="yes">	  
				<?php } ?>
			<input type="checkbox" id="agreement" name="agree" <?php if ($cart->agreed) { echo " checked='checked'"; } ?> >
			I agree to The <a href="/sfCartPlugin/terms.html" class="trigger" style="color:#000;" target="_blank">Terms and Conditions</a>
			<?php if ($cart->termserror != "") { echo "<div class='warning'>".$cart->termserror."</div>"; unset($cart->termserror); } ?>
			<div style="clear:both"></div>

			<?php $protocol = 'http://'; if (!empty($_SERVER['HTTPS'])) { $protocol = 'https://'; }
			echo "<input type='hidden' id='checkout-page' name='checkout_page' value='" . $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . "' />"; ?>    
			<div id="paypalform">
			<input type='image' id='paypalcheckout' name='paypalcheckout' value='Paypal Checkout' src="/sfCartPlugin/images/paypal.gif" />
			<br/>
			<img alt="cc" src="/sfCartPlugin/images/ccMC.gif">
			<img alt="cc" src="/sfCartPlugin/images/ccVisa.gif">
			<img alt="cc" src="/sfCartPlugin/images/ccAmex.gif">
			<img alt="cc" src="/sfCartPlugin/images/ccDiscover.gif">
			<img alt="cc" src="/sfCartPlugin/images/ccEcheck.gif">
			<img alt="cc" src="/sfCartPlugin/images/ccPayPal.gif">
			</div>
			</form>     
						
			<?php }
			
			
      echo "</div>";