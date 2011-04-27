<?php

class cart {

	private static $cart = null;

	//get cart instance
	private static function init_cart() {
		if (self::$cart == null) { self::$cart = sfContext::getInstance()->getUser()->getAttribute('cart', new cartCore()); }
	}

	//Shows all level 1 category nodes in list format - used as menu bar
	public static function display_root_categories() {
		$treeObject = Doctrine_Core::getTable('Category')->getTree();
		$rootColumnName = $treeObject->getAttribute('rootColumnName');
		echo "<div id='sfcart_menu'><ul>";
		foreach ($treeObject->fetchRoots() as $root) {
			$options = array('root_id' => $root->$rootColumnName);
			foreach($treeObject->fetchTree($options) as $node) {
				if ($node['level'] == 1) {
					echo "<li><a href='/store/". $node->getCrumb()  ."'>" . $node['name'] . "</a></li>";
				}
			}
		}
		echo "</ul></div><div style='clear:both'></div>";
	}

	//show breadcrumb trail of specified category
	public static function display_breadcrumbs($categoryId) {
		if (isset($categoryId)) {
			$node = Doctrine_Core::getTable('Category')->findOneById($categoryId)->getNode();
			$p = $node;
			$i = 0;
			while ($p != null) {
				$breadcrumbs[$i] = "<a href='".url_for('/store/' . $p->getRecord()->getCrumb() ) ."'>&#187;&nbsp;&nbsp;" . $p->getRecord()->getName() . "</a>";
				if ($p->hasParent()) { $p = $p->getParent()->getNode(); $i++; }
				else { $p = null; }
			}
			$i--;  //remove root node "categories"
			while($i >= 0) {
				echo $breadcrumbs[$i];
				$i--;
			}
		}
	}

	//Side Categories Menu
	//Shows up to three level of categories - based on the categoryId selected
	//TODO: This needs to be reviewed.  Has to be a better way to display infinite nested sets efficiently.
	public static function display_sub_categories($categoryId) {
		if (isset($categoryId)) {
			$node = Doctrine_Core::getTable('Category')->findOneById($categoryId)->getNode();

			$child1 = false;
			$child2 = false;
			$parent1 = false;
			$mainNode = null;

			//get up to two level of children categories
			if ($node->hasChildren()) {
				$child1 = true;
				foreach ($node->getChildren() as $child) {
					if ($child->getNode()->hasChildren()) {
						$child2 = true;
						$mainNode = $node;
						break;
					}
				}
			}
			//try to use first parent if child2 not found
			if (!$child2)  {
				if ($node->hasParent()) {
					$parent1 = true;
					$mainNode = $node->getParent()->getNode();
				}
			}
			//try to use second parent if no children
			if (!$child1) {
				if (($parent1) && ($mainNode->hasParent())) {
					$mainNode = $mainNode->getParent()->getNode();
				}
			}
		}
		else {
			//if no category selected, display from root
			$treeObject = Doctrine_Core::getTable('Category')->getTree();
			$rootColumnName = $treeObject->getAttribute('rootColumnName');
			foreach ($treeObject->fetchRoots() as $root) {
				$mainNode = $root->getNode();
				break;
			}
		}

		//display category tree
		if ($mainNode->hasChildren()) {
			$children = $mainNode->getChildren();
			echo "<h2>".$mainNode->getRecord()->getName()."</h2>";
			echo "<ul id='sfcart_submenu'>";
			foreach ($children as $child) {
				if ($child->getId() == $categoryId) { echo "<li><a class='selected' href='/store/" . $child->getCrumb() ."'>" . $child['name'] . "</a></li>"; }
				else { echo "<li><a href='/store/" . $child->getCrumb() ."'>" . $child['name'] . "</a></li>";}
				if ($child->getNode()->hasChildren()) {
					echo "<ul>";
					foreach ($child->getNode()->getChildren() as $child2) {
						if ($child2->getId() == $categoryId) { echo "<li><a class='selected' href='/store/". $child2->getCrumb() ."'>" . $child2['name'] . "</a></li>"; }
						else { echo "<li><a href='/store/". $child2->getCrumb() ."'>" . $child2['name'] . "</a></li>"; }
					}
					echo "</ul>";
				}
			}
			echo "</ul>";
		}
		else {
			echo "<p>No categories.</p>";
		}
		if ($mainNode->hasParent()) {
			$parent = $mainNode->getParent();
			echo "<a href='/store/" . $parent->getCrumb()."'><- Back to ".$parent['name']."</a>";
		}

	}

	//refreshes a category node (admin panel) - used via ajax call
	public static function update_node($categoryid) {
		$node = Doctrine_Core::getTable('Category')->findOneById($categoryid)->getNode();
		$record = $node->getRecord();

		if (!$node->isRoot()) {
			echo $record->getName();

			if (count($record->Products) != 1)  {
				echo "<a href='/cartadmin/productIndex?filter=".$record->getId()."'>(" . count($record->Products) . " products)</a>";
			} else {
				echo "<a href='/cartadmin/productIndex?filter=".$record->getId()."'>(" . count($record->Products) . " product)</a>";
			}
			echo "<span class='categoryactions'>";

			$configuration = sfContext::getInstance()->getConfiguration();
			$configuration->loadHelpers(array('Url', "Tag"));

			echo link_to('Delete', 'cartadmin/categoryDelete?id='.$record->getId(), array('method' => 'delete', 'confirm' => 'Are you sure? (Please note that deleting a category will also delete all of its related child categories.)'));
			echo '<a href="' . url_for('cartadmin/categoryNew?parent=' . $record->getId()) . '">Insert Category Below</a>';
			echo '<a href="#" class="linkproduct">Link Product</a>';
			echo '<a href="'.url_for('cartadmin/categoryEdit?id='. $record->getId()) .'">Edit</a></span>';

			echo "<div class='productrow' id='productrow_".$record->getId()."'>";
			//display related products
			foreach ($record->Products as $product) {
				echo "<span class='productrowitem'>";
				echo $product->getName();
				echo "<a href='".url_for('cartadmin/productUnlink?categoryid=' . $record->getId() . '&productid=' . $product->getId())."' class='unlinkproduct' id='unlinkproduct_".$product->getId()."'>Remove</a>";
				echo "</span>";
			}
			echo "<div style='clear:both'></div>";
			echo "</div>";
			echo "<div style='clear:both'></div>";
		}
	}

	//shows a category node (admin panel) - recurses for all descendants of that node.  Can be used to draw entire tree, when specifying root id.
	public static function display_node($categoryid, $node = null) {
		$isRoot = false;
		if ($node == null) {
			$node = Doctrine_Core::getTable('Category')->findOneById($categoryid)->getNode();
			if ($node->isRoot()) {
				$isRoot = true;
			}
		}
		$record = $node->getRecord();

		if (!$isRoot) {
			if ($node->getLevel() == 1) { echo "<li class='tree_item root_item' style='position: relative;' id='list_". $record->getId() . "'>"; }
			else { echo "<li class='tree_item' style='position: relative;' id='list_". $record->getId() . "'>"; }
			echo "<div class='listitem' id='listitem_".$record->getId()."'>";
				
			echo $record->getName();

			if ((count($record->Products) != 0) && (count($record->Products) > 1)) {
				echo "<a href='/cartadmin/productIndex?filter=".$record->getId()."'>(" . count($record->Products) . " products)</a>";
			} else {
				echo "<a href='/cartadmin/productIndex?filter=".$record->getId()."'>(" . count($record->Products) . " product)</a>";
			}
			echo "<span class='categoryactions'>";

			$configuration = sfContext::getInstance()->getConfiguration();
			$configuration->loadHelpers(array('Url', "Tag"));

			echo link_to('Delete', 'cartadmin/categoryDelete?id='.$record->getId(), array('method' => 'delete', 'confirm' => 'Are you sure? (Please note that deleting a category will also delete all of its related child categories.)'));
			//echo '<a href="' . url_for('cartadmin/categoryNew?parent=' . $record->getId()) . '" class="insertcategory">Insert Category Below</a>';
			echo '<a href="#" class="insertcategory">Insert Category Below</a>';
			echo '<a href="#" class="linkproduct">Link Product</a>';
			echo '<a href="'.url_for('cartadmin/categoryEdit?id='. $record->getId()) .'">Edit</a></span>';

			echo "<div class='productrow' id='productrow_".$record->getId()."'>";
			//display related products
			foreach ($record->Products as $product) {
				echo "<span class='productrowitem'>";
				echo $product->getName();
				echo "<a href='".url_for('cartadmin/productUnlink?categoryid=' . $record->getId() . '&productid=' . $product->getId())."' class='unlinkproduct' id='unlinkproduct_".$product->getId()."'>Remove</a>";
				echo "</span>";
			}
			echo "<div style='clear:both'></div>";
			echo "</div>";
			echo "<div style='clear:both'></div>";
			echo "</div>";
				

			if ($node->hasChildren()) {
				echo "<ol>";
				foreach ($node->getChildren() as $child) {
					self::display_node($child->getId(), $child->getNode());
				}
				echo "</ol>";
			}
				
		}
		else {
			if ($node->hasChildren()) {
				echo "<ol class='sortable'>";
				foreach ($node->getChildren() as $child) {
					self::display_node($child->getId(), $child->getNode());
				}
				echo "</ol>";
			}
		}
	}

	//Show administrative category tree (full tree)
	public static function display_category_tree() {
		$treeObject = Doctrine_Core::getTable('Category')->getCategoriesWithProducts()->getTable()->getTree();
		$rootColumnName = $treeObject->getAttribute('rootColumnName');
		foreach ($treeObject->fetchRoots() as $root) {
			self::display_node($root->$rootColumnName);  //recursively creates entire tree
		}
	}

	//show products linked to a category
	public static function display_products($catid) {
		$category = null;
		if ($catid == null) {
			$products = Doctrine::getTable('product')->createQuery('a')->execute();
		}
		else {
			$category = Doctrine::getTable('Category')->find($catid);
		}

		if (count($category) > 0) {
			$products = $category->getProducts();
		}
		else { $category = null; }

		if (count($products) > 0) {
			foreach ($products as $product) {
				include_partial('cart/product', array('product' => $product, 'category' => $category));
			}
		}
		else {
			echo "<p>No products to display in this category.</p>";
		}
	}

	//Show the shopping cart
	public static function display_cart($notice="") {
		self::init_cart();

		echo "<div id='sfcart'>";
		echo $notice;
		echo "<form method='post' action='/updatecart'>";
		echo "<fieldset>";
		echo "<div id='sfcart_maxheight'>";

		if(self::$cart->getItemCount() > 0)
		{
			foreach(self::$cart->get_contents() as $item) {

				echo "<div class='sfcart_item'>";
				if ($item['photo_path'] == "") {
					echo "<div class='noimagetiny'><p>No image</p></div>";
				}
				else {
					echo "<img src='/uploads/sfCart/products/48x48/" . $item['photo_path'] . "' alt='productthumb' />";
				}
				echo "<span class='sfcart_itemname'><a href='" . $item['url'] . "'>" . $item['name'] . "</a></span><br/>";
				echo "<span class='sfcart_itemprice'><span style='text-decoration: underline; display: inline;'>Price</span>: $" . $item['price'] . "</span></div>";
				echo "<div class='sfcart_itemactions'><span class='sfcart_itemquantity'>Qty:" . $item["qty"];
				echo "<input type='hidden' size='2' id='cart_item_id_" . $item["id"] . "' name='cart_item_qty[ ]' value='" . $item["qty"] . "' /></span>";
				echo "<span class='sfcart_itemremove'><input type='hidden' size='2' name='remove_item' value='" . $item['id'] . "' />";
				echo "<a href='/updatecart?cart_remove=" . $item['id'] . "'>Remove</a></span>";
				echo "<div style='clear:both'></div></div>";
				echo "<input type='hidden' name='cart_item_name[ ]' value='" . $item['name'] . "' />";
				echo "<input type='hidden' name='cart_item_price[ ]' value='" . $item['price'] . "' />";

				//include_partial('cart/cartItem', array('item' => $item)); 	 << Doesn't work with ajax calls.
			}
			echo "</div>";
				
			echo "<span id='sfcart_cartsubtotal'>Subtotal: <strong>&#36;" . number_format(self::$cart->getTotal(), 2) . "</strong><br/></span>";
			echo "<span id='emptycart'><a class='cart-remove' href='/updatecart?cart_empty=1'>Empty Cart</a></span>";
			echo "</fieldset></form>";
				
			echo "<form method='post' action='/checkout'>";
			echo "<input type='submit' value='Checkout' />";
			echo "</form>";
		}
		else {
			echo "<p id='sfcart_empty'>Your cart is empty.</p>";
			echo "</div>";
			echo "</fieldset></form>";
		}
		echo "</div>";
	}

	//show the checkout
	public static function display_checkout($form=null) {
		self::init_cart();
		if ((self::$cart->getZipcode() != "") && (count(self::$cart->getShippingQuotes()) == 0)) { self::$cart->refresh_shipping_quotes(); }
		if(self::$cart->getItemCount() > 0)
		{
			include_partial('cart/checkout', array('cart' => self::$cart));
		}
		else {
			echo "<p>Your cart is currently empty.</p>";
		}
	}

	//show cart pricing summary
	public static function display_summary() {
		self::init_cart();
		echo "<span id='sfcart_subtotal'>Subtotal: <strong>&#36; " . number_format(self::$cart->getTotal(),2) ."</strong><br/></span><div class='clear'></div>";
		echo "<span id='sfcart_shipping'>Shipping: <strong>&#36; " . number_format(self::$cart->getSelectedQuote(),2) ."</strong><br/></span><div class='clear'></div>";
		echo "<span id='sfcart_tax'>Tax (" . self::$cart->getTax() . "%): <strong>&#36; " . number_format(self::$cart->getTotal()*(self::$cart->getTax()/100),2) . "</strong><br/></span><div class='clear'></div>";

		if (self::$cart->getCoupon() != "" &&  self::$cart->getCouponDiscount() > 0) {
			echo "<span id='sfcart_coupon'>Coupon: <strong>-&#36; ". number_format(self::$cart->getCouponDiscount()) . "</strong><br/></span><div class='clear'></div>";
		}
		echo "<span id='sfcart_total'>Total: <strong>&#36; ". number_format((self::$cart->getTotal()*(1 + self::$cart->getTax()/100)) + self::$cart->getSelectedQuote() - self::$cart->getCouponDiscount(),2) . "</strong><br/></span>";
	}



}
?>
