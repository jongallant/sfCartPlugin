<div class="sfcart_item">

	<?php if ($item['photo_path'] == "") { ?>
		<div class='noimagetiny'><p>No image</p></div>
	<?php } else { ?>
		<img src='<?php echo public_path('uploads/sfCart/products/48x48/' . $item['photo_path'])?>' alt='productthumb' />
	<?php } ?>
	<span class="sfcart_itemname"><a href="<?php echo $item['url']?>"><?php echo $item['name']?></a></span><br/>
	<span class="sfcart_itemprice"><span style="text-decoration: underline; display: inline;">Price</span>: $<?php echo $item['price']?></span>
</div>

<div class="sfcart_itemactions">
	<span class="sfcart_itemquantity">
		Qty:  <?php echo $item["qty"] ?>
		<input type='hidden' size='2' id='cart_item_id_<?php echo $item["id"]?>' name='cart_item_qty[ ]' value='<?php echo $item["qty"]?>' />
	</span>
	<span class="sfcart_itemremove">
		<input type='hidden' size='2' name='remove_item' value='<?php echo $item['id'] ?>' />
		<a href='/updatecart?cart_remove=<?php echo $item['id']?>'>Remove</a>
	</span>
	<div style="clear:both"></div>
</div>

<input type='hidden' name='cart_item_name[ ]' value='<?php echo $item['name']?>' />
<input type='hidden' name='cart_item_price[ ]' value='<?php echo $item['price']?>' />