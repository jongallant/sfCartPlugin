<div class="sfcart_products">     
  <form method="post" class="sfCartAdd" action="/updatecart">
    <fieldset>
      <input type="hidden" name="productid" value="<?php echo $product->getId() ?>" />
      <input type="hidden" name="productname" value='<?php echo $product->getName() ?>' />
      <input type="hidden" name="productprice" value='<?php echo $product->getPrice() ?>' />
      <input type="hidden" name="productpath" value="<?php echo $product->getPhotoPath() ?>" />
      <input type="hidden" name="productweight" value="<?php echo $product->getWeight() ?>" />
      <input type="hidden" name="producturl" value="#" />

      <div class="sfcart_productimage">
        <a href="<?php echo public_path('uploads/sfCart/products/' . $product->getPhotoPath()) ?>">
        <?php 
          if ($product->getPhotoPath()) { echo "<img src='". public_path('uploads/sfCart/products/172x129/' . $product->getPhotoPath()) . "' />"; } 
          else { echo "<div class='noimagesm'><p>No image</p></div>"; } 
        ?>
        </a>								
      </div>
        
      <div class="sfcart_producttext">
        <?php if (isset($category)) { ?>   
          <a class="sfcart_producttitle" href="/store/<?php echo $category->getCrumb() ?>/product/<?php echo $product->getSlug() ?>"><?php echo $product->getName() ?></a>
        <?php } else { ?>
          <a class="sfcart_producttitle" href="/store/product/<?php echo $product->getSlug() ?>"><?php echo $product->getName() ?></a>
        <?php } ?>
        <p><?php echo $product->getCode() ?></p>
 
        <div class="sfcart_productprice"><span><?php echo "&#036;" . $product->getPrice(); ?></span></div>
        <div class='sfcart_buy'>
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
      </div>  
    </fieldset>
  </form>
</div>