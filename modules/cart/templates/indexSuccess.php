<div id="sfcart_container">

  <?php cart::display_root_categories(); ?>

  <div id="sfcart_left">
    <?php cart::display_sub_categories($catid); ?>
    <div style="clear:both"></div>
  </div>
  
  <div id="sfcart_center">
    <div id="sfcart_breadcrumbs">
      <?php cart::display_breadcrumbs($catid); ?>
      <div style="clear:both"></div>
    </div>
    <?php if (isset($product)) { 

      $product->display();
    } else {   
      cart::display_products($catid); 
    } ?>
    <div style="clear:both"></div>
  </div>
  
  <div id="sfcart_right">
    <?php cart::display_cart(); ?>
  </div>
 
  <div style="clear:both"></div>
</div>



