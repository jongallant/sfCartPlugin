$(document).ready(function(){

     $('form.sfCartAdd').submit(function(){
      
		$(this).find('span.sfcart_loading').show();
		
		var itemId = $(this).find('input[name=productid]').val();
		var itemPrice = $(this).find('input[name=productprice]').val();
		var itemName = $(this).find('input[name=productname]').val();
		var itemQty = $(this).find('input[name=productqty]').val();
		var itemAdd = $(this).find('input[name=productadd]').val();
		var itemPhotoPath = $(this).find('input[name=productpath]').val();
		var itemWeight = $(this).find('input[name=productweight]').val();
    var itemUrl = $(this).find('input[name=producturl]').val();
    
		$.post('/updatecart', { "productid": itemId, "productprice": itemPrice, "productname": itemName, "productqty": itemQty, "productadd" : itemAdd, "productweight": itemWeight, "productpath": itemPhotoPath, "producturl": itemUrl }, function(data) {
		
			$('#sfcart_right').html(data);
			$('span.sfcart_loading').hide();	
			
			$('div.cartwarning').hide();
			$('div.cartwarning').animate({opacity:'show', height:'show', easing:'swing'},600);
			$('div.cartwarning').animate({opacity: 1.0}, 6000).animate({opacity: 'hide', height:'hide', easing:'swing'},600);

			$('div.cartmessage').hide();	
			$('div.cartmessage').animate({opacity:'show', height:'show', easing:'swing'}, 600);
			$('div.cartmessage').animate({opacity: 1.0}, 6000).animate({opacity: 'hide', height:'hide', easing:'swing'},600);		
		});
		
		
		return false;
	});
  

	//enter keypress - do nothing
	$('#cart').keydown(function(e) {
		if(e.which == 13) {
		return false;
		}
	});
   
	});

