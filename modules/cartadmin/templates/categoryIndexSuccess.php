<div id="sfCartAdminContainer">
 
  <?php echo include_partial('cartadmin/menu') ?>

  <h1>Manage Categories</h1>

  <a href="<?php echo url_for('cartadmin/categoryNew') ?>">Create New Category</a>

  <div id="save">
    <input type="submit" name="updatecategories" id="updatecategories" value="Save Changes" />
    <span id="messagebox"></span>
  </div>

  <div class="categorylist">
      <?php cart::display_category_tree();   ?>
  </div>

</div>
