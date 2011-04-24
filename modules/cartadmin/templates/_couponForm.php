<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('cartadmin/'.($form->getObject()->isNew() ? 'couponCreate' : 'couponUpdate').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('cartadmin/couponIndex') ?>">Back to list</a>
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Delete', 'cartadmin/couponDelete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <tr>
        <th><?php echo $form['code']->renderLabel() ?></th>
        <td>
          <?php echo $form['code']->renderError() ?>
          <?php echo $form['code'] ?>
        </td>
      </tr>
		  <tr>
        <th><?php echo $form['discount']->renderLabel() ?></th>
        <td>
          <?php echo $form['discount']->renderError() ?>
          <?php echo $form['discount'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['start']->renderLabel() ?></th>
        <td>
          <?php echo $form['start']->renderError() ?>
          <?php echo $form['start'] ?>
        </td>
      </tr>
		  <tr>
        <th><?php echo $form['end']->renderLabel() ?></th>
        <td>
          <?php echo $form['end']->renderError() ?>
          <?php echo $form['end'] ?>
        </td>
      </tr>			
      <tr>
        <th><?php echo $form['active']->renderLabel() ?></th>
        <td>
          <?php echo $form['active']->renderError() ?>
          <?php echo $form['active'] ?>
        </td>
      </tr>
		  <tr>
        <th><?php echo $form['minprice']->renderLabel() ?></th>
        <td>
          <?php echo $form['minprice']->renderError() ?>
          <?php echo $form['minprice'] ?>
        </td>
      </tr>			
    </tbody>
  </table>
</form>
