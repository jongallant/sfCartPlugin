<?php 
$torender = array();
$count = 0;

foreach ($product->getCategories() as $category) {
  $crumbs = explode("/", $category->getCrumb());
  $torender[$count] = $crumbs;
  $count++;
}
?>

<div class="sfcart_product">     
  <form method="post" action="/updatecart">
    <fieldset>
      <input type="hidden" name="productid" value="<?php echo $product->getId() ?>" />
      <input type="hidden" name="productname" value='<?php echo $product->getName() ?>' />
      <input type="hidden" name="productprice" value='<?php echo $product->getPrice() ?>' />
      <input type="hidden" name="productpath" value="<?php echo $product->getPhotoPath() ?>" />
      <input type="hidden" name="productweight" value="<?php echo $product->getWeight() ?>" />
      <input type="hidden" name="producturl" value="#" />

      <div class="sfcart_productimage_view">
        <a href="<?php echo public_path('uploads/sfCart/products/' . $product->getPhotoPath()) ?>">
        <?php 
          if ($product->getPhotoPath()) { echo "<img src='". public_path('uploads/sfCart/products/300x200/' . $product->getPhotoPath()) . "' />"; } 
          else { echo "<div class='noimagelg'><p>No image</p></div>"; } 
        ?>
        </a>								
      </div>
      
      <div class='sfcart_buy_view'>
      <h1><?php echo $product->getName()?></h1>
      <p><?php echo $product->getCode() ?></p>
      <div class="sfcart_productprice"><span><?php echo "&#036;" . $product->getPrice(); ?></span></div>
     
          <?php 
          if ($product->getQuantity() <= 0) { ?>
            <p>This product has sold out</p>
          <?php } else { ?>
            <div class="sfcart_productquantity">
              <label>Qty: <input type="text" name="productqty" value="1" size="2" maxlength="3" style="width: 25px" /></label>
              <input type='submit' value='Add to Cart' />
            </div>	
            <span class='sfcart_loading'><img title="Loading" alt="Loading" src="<?php echo public_path('sfCartPlugin/images/indicator.gif') ?>" class="loadingimage"/></span>
        <?php } ?>
      </div>
      <div style="clear:both"></div>
      
      <div class="sfcart_description">
        <?php echo $product->getRaw('description'); ?>
      </div>

    </fieldset>
  </form>
</div>