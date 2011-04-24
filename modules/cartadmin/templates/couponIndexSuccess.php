<div id="sfCartAdminContainer">

  <?php echo include_partial('cartadmin/menu') ?>

  <h1>Manage Coupons</h1>

  <a href="<?php echo url_for('cartadmin/couponNew') ?>">Create New Coupon</a>  

	<table>
    <thead>
      <tr>
        <th>Id</th>
        <th>Code</th>
        <th>Discount</th>
        <th>Start</th>
        <th>End</th>
				<th>Active</th>
				<th>Min. Price</th>
      </tr>
    </thead>
    <tbody>
        
      <?php foreach ($coupons as $i => $coupon): ?>
      <tr class="<?php echo fmod($i, 2) ? 'even' : 'odd' ?>">
        <td><a href="<?php echo url_for('cartadmin/couponEdit?id='.$coupon->getId()) ?>"><?php echo $coupon->getId() ?></a></td>
				<td><?php echo $coupon->getCode() ?></td>
				<td><?php echo $coupon->getDiscount() ?></td>
				<td><?php echo $coupon->getStart() ?></td>
				<td><?php echo $coupon->getEnd() ?></td>
				<td><?php echo $coupon->getActive() ?></td>
				<td><?php echo $coupon->getMinPrice() ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>


