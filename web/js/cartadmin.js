$(document).ready(function(){

	$('#updatecategories').attr("disabled", true); 
	$('#messagebox').hide();

	$('.listitem').dblclick(function(e) {
		$products = $(this).find('.productrow');
		if ($products.is(":visible")) {
			$(this).find('.productrow').hide();
		}
		else {
			$(this).find('.productrow').show();
		}
	});
	
  
	function rebind(node) {
		node.find('.unlinkproduct').bind('click', function() {
			 $categoryId = $(this).parents('li').attr('id');
			 $categoryId = $categoryId.replace("list_", "");
			 $productId = $(this).attr('id').replace('unlinkproduct_', "");
			 unlinkProduct($categoryId, $productId, $(this).parents('li'));
			 return false;
		});

		node.find('.linkproduct').bind('click', function() {
			$categoryId = $(this).parents('li').attr('id');
			$categoryId = $categoryId.replace("list_", "");      
			$(this).replaceWith('<input id="products'+ $categoryId +'" />');
			$('#products'+$categoryId).focus();
			$('#products'+$categoryId).autocomplete({
				source: "/cartadmin/productSearch",
				minLength: 2,
				select: function( event, ui ) {
					$categoryId = $(this).parents('li').attr('id');
					$categoryId = $categoryId.replace("list_", "");
					linkProduct($categoryId, ui.item.id, $(this).parents('li'));
			}
			});
		});
	}

	$('.unlinkproduct').bind('click', function() {
		 $categoryId = $(this).parents('li').attr('id');
		 $categoryId = $categoryId.replace("list_", "");
		 $productId = $(this).attr('id').replace('unlinkproduct_', "");
		 unlinkProduct($categoryId, $productId, $(this).parents('li'));
		 return false;
	});
  
	function unlinkProduct(categoryid, productid, node) {
		$.post('/cartadmin/productUnlink', { "productid": productid, "categoryid": categoryid  }, function(data) {
				node.find('div#listitem_'+categoryid).html(data);
				node.find('div#productrow_'+categoryid).show();
				rebind(node);
		});
		return false;
	}
	
  
  function insertCategory(name, categoryid, node) {
    $.post('/cartadmin/insertCategory', { "categoryname": name, "categoryid": categoryid  }, function(data) {
			//node.find('div#listitem_'+categoryid).html(data);
			//rebind(node);
      location.reload();
		});
    return true;
  }
  
  $('.insertcategory').bind('click', function() {
    $categoryId = $(this).parents('li').attr('id');
    $categoryId = $categoryId.replace("list_", "");      
    $(this).replaceWith('<input id="categories'+ $categoryId +'" />');
    $('#categories'+$categoryId).focus();
   
    $('#categories'+$categoryId).bind("keydown", function(event) {
      var keycode = (event.keyCode ? event.keyCode : (event.which ? event.which : event.charCode));
      if (keycode == 13) { // keycode for enter key
        insertCategory($(this).val(), $categoryId, $(this).parents('li'));
      } 
    }); 
  }); 

  
  
  
  
  $('.linkproduct').bind('click', function() {
    $categoryId = $(this).parents('li').attr('id');
    $categoryId = $categoryId.replace("list_", "");      
    $(this).replaceWith('<input id="products'+ $categoryId +'" />');
    $('#products'+$categoryId).focus();
    $('#products'+$categoryId).autocomplete({
      source: "/cartadmin/productSearch",
      minLength: 2,
      select: function( event, ui ) {
        $categoryId = $(this).parents('li').attr('id');
        $categoryId = $categoryId.replace("list_", "");
        linkProduct($categoryId, ui.item.id, $(this).parents('li'));
  }
  });
  return false;
  });

  function linkProduct(categoryid, productid, node) {
    $.post('/cartadmin/productLink', { "productid": productid, "categoryid": categoryid  }, function(data) {
        node.find('div#listitem_'+categoryid).html(data);
        node.find('div#productrow_'+categoryid).show();
        rebind(node);
    return false;
  });
       
  return false;
  }

  $('ol.sortable').nestedSortable({
    disableNesting: 'no-nest',
    forcePlaceholderSize: true,
    handle: 'div',
    items: 'li',
    opacity: .6,
    placeholder: 'placeholder',
    tabSize: 25,
    tolerance: 'pointer',
    toleranceElement: '> div'
  });

  $('#updatecategories').click(function(e) {
    arraied = $('ol.sortable').nestedSortable('updatecategories', {startDepthCount: 0});
    $.post('/cartadmin/categoryUpdateTree', { "tree": arraied  }, function(data) {
        $('#messagebox').html(data);
        $('#messagebox').animate({opacity:'show'},700);
        $('#updatecategories').attr("disabled", true);
         $('#messagebox').animate({opacity: 1.0}, 4000).animate({opacity: 'hide'},600);
    });
      return false;
  }); 
  
  $('#updateproducts').click(function(e) {
      var $inputs = $('#productupdatebatch :input');
      
      var weights = {}; //weight,price,qty
      var prices = {};
      var quantities = {};
      var states = {};
      var saleprices = {};
      var codes = {};
      $error = false;
      
      $inputs.each(function() {
        $name = $(this).attr('name');
        $value = $(this).val();
        if ($name.search("productweight_") != -1) {
          $(this).css('background-color', "#fff"); 
          $(this).css('border', "solid 1px #a5a5a5");
          if (isNaN($value)) { 
            $(this).css('background-color', "#f9d5d5"); 
            $(this).css('border', "solid 1px #a41010"); 
            $error=true;
            $(this).attr("title", "Invalid character");
            $(this).tipsy({trigger: 'focus', gravity: 's'});
          }
          else { weights[$name.replace("productweight_", "")] = $value; }
        }
        if ($name.search("productprice_") != -1) {
          $(this).css('background-color', "#fff"); 
          $(this).css('border', "solid 1px #a5a5a5");
          if (isNaN($value)) {  
            $(this).css('background-color', "#f9d5d5"); 
            $(this).css('border', "solid 1px #a41010"); 
            $error=true;
            $(this).attr("title", "Invalid character");
            $(this).tipsy({trigger: 'focus', gravity: 's'});
          }
          else { prices[$name.replace("productprice_", "")] = $value;}
        }
        if ($name.search("productcode_") != -1) {
          codes[$name.replace("productcode_", "")] = $value;
        }
        if ($name.search("productsaleprice_") != -1) {
          $(this).css('background-color', "#fff"); 
          $(this).css('border', "solid 1px #a5a5a5");
          if (isNaN($value)) {  
            $(this).css('background-color', "#f9d5d5"); 
            $(this).css('border', "solid 1px #a41010"); 
            $error=true;
            $(this).attr("title", "Invalid character");
            $(this).tipsy({trigger: 'focus', gravity: 's'});
          }
          else { saleprices[$name.replace("productsaleprice_", "")] = $value;}
        }
        if ($name.search("productquantity_") != -1) {
          $(this).css('background-color', "#fff"); 
          $(this).css('border', "solid 1px #a5a5a5");
          if (isNaN($value)) {  
            $(this).css('background-color', "#f9d5d5"); 
            $(this).css('border', "solid 1px #a41010"); 
            $error=true;
            $(this).attr("title", "Invalid character");
            $(this).tipsy({trigger: 'focus', gravity: 's'});
          }
          else { quantities[$name.replace("productquantity_", "")] = $value;}
        }
        if ($name.search("productactive_") != -1) {
          if ($(this).attr('checked')) { 
            states[$name.replace("productactive_", "")] = 1;
          }
          else {
            states[$name.replace("productactive_", "")] = 0;
          }

        }
      });    

      if ($error) { 
        $('#messagebox').html("There was an error saving the products.");
        var options = {"color": "#f9d5d5"};
        $('#messagebox').css('background-color', "#fff");
        $("#messagebox").stop(true, false).show("highlight", options, 5000, closeMessage);
      }
      else {
        //$.post('/cartadmin/productUpdateBatch', { "weights": weights, "prices": prices, "saleprices": saleprices, "quantities": quantities, "states": states, "codes": codes }, function(data) {      
        $.post('/cartadmin/productUpdateBatch', { "weights": weights, "prices": prices, "saleprices": saleprices, "quantities": quantities, "states": states, "codes": codes }, callbackHandler, 'json');
      }

      return false;
  }); 
  
		function closeMessage() {
			$("#messagebox").delay(2000).animate({opacity:'hide'},1000); 
    }

    
    function callbackHandler(data) { 
      for (var i in data) { 
        if ($('tr#productrow_' + data[i]).attr("class") == "odd") {  $('tr#productrow_' + data[i] + ' td').css('background-color', "#f0f0f0"); }
        else{ $('tr#productrow_' + data[i] + ' td').css('background-color', "#e4e4e4");  }
        $('tr#productrow_' + data[i] + ' td').stop(true, false).show("highlight", options, 5000);
      }
      
      if (data.length == 0) {
        $('#messagebox').html("No products require updating.");
      }
      else {
        $('#messagebox').html("Products have been updated.");
      }
      $('#messagebox').css('background-color', "#fff");
      var options = {};
      $("#messagebox").stop(true, false).show("highlight", options, 5000, closeMessage);
    }


 
 
if ( window.CKEDITOR )
{
	(function()
	{
		var showCompatibilityMsg = function()
		{
			var env = CKEDITOR.env;

			var html = '<p><strong>Your browser is not compatible with CKEditor.</strong>';

			var browsers =
			{
				gecko : 'Firefox 2.0',
				ie : 'Internet Explorer 6.0',
				opera : 'Opera 9.5',
				webkit : 'Safari 3.0'
			};

			var alsoBrowsers = '';

			for ( var key in env )
			{
				if ( browsers[ key ] )
				{
					if ( env[key] )
						html += ' CKEditor is compatible with ' + browsers[ key ] + ' or higher.';
					else
						alsoBrowsers += browsers[ key ] + '+, ';
				}
			}

			alsoBrowsers = alsoBrowsers.replace( /\+,([^,]+), $/, '+ and $1' );

			html += ' It is also compatible with ' + alsoBrowsers + '.';

			html += '</p><p>With non compatible browsers, you should still be able to see and edit the contents (HTML) in a plain text field.</p>';

			var alertsEl = document.getElementById( 'alerts' );
			alertsEl && ( alertsEl.innerHTML = html );
		};

		var onload = function()
		{
			// Show a friendly compatibility message as soon as the page is loaded,
			// for those browsers that are not compatible with CKEditor.
			if ( !CKEDITOR.env.isCompatible )
				showCompatibilityMsg();
		};

		// Register the onload listener.
		if ( window.addEventListener )
			window.addEventListener( 'load', onload, false );
		else if ( window.attachEvent )
			window.attachEvent( 'onload', onload );
	})();
}

   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
   
	});

