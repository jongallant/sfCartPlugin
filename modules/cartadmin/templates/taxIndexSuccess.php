<div id="sfCartAdminContainer">

  <?php echo include_partial('cartadmin/menu') ?>

  <h1>Manage Tax Rates</h1>

  <a href="<?php echo url_for('cartadmin/taxNew') ?>">Create New Tax Rate</a>

	<table>
    <thead>
      <tr>
        <th>Region Code</th>
        <th>Rate</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
    
    
      <?php foreach ($taxes as $i => $tax): ?>
      <tr class="<?php echo fmod($i, 2) ? 'even' : 'odd' ?>">
        <td class="smallcolumn"><?php echo $tax->getRegionCode() ?></td>
        <td class="smallcolumn"><?php echo $tax->getRate() ?></td>
        <td class="smallcolumn"><a href="<?php echo url_for('cartadmin/taxEdit?id='.$tax->getId()) ?>">Edit</a></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

</div>


