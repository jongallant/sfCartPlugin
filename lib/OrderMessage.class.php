<?php

class OrderMessage extends Swift_Message
{
  public function __construct($cartitems)
  {
		$subject = "Paypal Order Request";
    
    $body = "A order has been checked out to Paypal.  The payment has not yet been processed by Paypal.\n\r";
    $body = "You will receive a notice from Paypal if and when the customer makes his payment.\n\r";
		$body .= "-------------------------------------------------\n\r";
		$body .= "Cart Items:\n\r";
		$total = 0;
		foreach ($cartitems as $cartitem) { 
			$total = $total + $cartitem['subtotal'];
			$body .= $cartitem['qty'] . "  x  " . $cartitem['name'] . "  @  $" . $cartitem['price'] . "\n\r";
		}
		$body .= "-------------------------------------------------\n\r";
		$body .= "Total:\t $" . $total . "\n\r";
		
		parent::__construct($subject, $body);
		$this->setFrom(array('mailer@yourdomain.com' => 'yourdomain.com'));
		$this->setTo(array('yourname@yourdomain.com' => 'Your Name'));
  }
}



?>
