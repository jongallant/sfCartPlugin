<div id="sfCartAdminContainer">

  <?php echo include_partial('cartadmin/menu') ?>

  <h1>Products List</h1>

  <div class="filter">
  <?php 
    if (isset($filter)) { $filterForm = new PluginCategorySelectionForm($filter); }
    else { $filterForm = new PluginCategorySelectionForm(); }
    echo include_partial('cartadmin/productFilterForm', array('form' => $filterForm)); 
  ?>
  </div>
  
  <a href="<?php echo url_for('cartadmin/productNew') ?>">Create New Product</a>

  <form method="post" action="/cartadmin/productUpdateBatch" id="productupdatebatch"> 
    
  <div id="save">
    <input type="submit" name="updateproducts" id="updateproducts" value="Save Changes" />
    <span id="messagebox"></span>
  </div>
  
  <?php if ($products != null) { ?>
  <table id="producttable">
    <thead>
      <tr>
        <th></th>
        <th>Name</th>
        <th>Code</th>
        <th>Weight</th>
        <th>Price</th>
        <th>Sale Price</th>
        <th>Quantity</th>
        <th>Active</th>
        <th>Slug</th>
        <th>Categories</th>
        <th></th>
      </tr>
    </thead>
    <tbody>

      <?php foreach ($products as $i => $product): ?>     

      <tr class="<?php echo fmod($i, 2) ? 'even' : 'odd' ?>" id="productrow_<?php echo $product->getId() ?>">
        <?php if ($product->getPhotoPath() != "") { ?>
          <td style="width: 48px;"><img src='<?php echo public_path('uploads/sfCart/products/48x48/' . $product->getPhotoPath()) ?>' alt="productthumb" /></td>
        <?php } else { ?>
          <td></td>
        <?php } ?>
        <td><a href="<?php echo url_for('cartadmin/productEdit?id='.$product->getId()) ?>"><?php echo $product->getName() ?></a></td>
        <td class="mediumcolumn"><input type="text" name="productcode_<?php echo $product->getId(); ?>" value="<?php echo $product->getCode() ?>" /></td>
        <td class="smallcolumn"><input type="text" name="productweight_<?php echo $product->getId(); ?>" value="<?php echo $product->getWeight() ?>" /></td>
        <td class="smallcolumn"><input type="text" name="productprice_<?php echo $product->getId(); ?>" value="<?php echo $product->getPrice() ?>" /></td>
        <td class="smallcolumn"><input type="text" name="productsaleprice_<?php echo $product->getId(); ?>" value="<?php echo $product->getSalePrice() ?>" /></td>
        <td class="smallcolumn"><input type="text" name="productquantity_<?php echo $product->getId(); ?>" value="<?php echo $product->getQuantity() ?>" /></td>
        <td class="smallcolumn"><input type="checkbox" name="productactive_<?php echo $product->getId(); ?>"<?php if ($product->getActive()){ echo " checked='checked'";} ?>" /></td>
        <td><?php echo $product->getSlug() ?></td>
        <td class='sfcart_floatcolumn'><?php
        
          foreach ($product->getCategories() as $category) { 
            echo "<a href='". url_for('cartadmin/productIndex?filter=' . $category['id']) ."'>" . $category['name'] . "</a>";
          } 
        ?></td>
        <td class="smallcolumn"><a href="<?php echo url_for('cartadmin/productEdit?id='.$product->getId()) ?>">Edit</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  
  </form>
  
  <?php  } else { echo "<p>No products</p>"; }?>

</div>
