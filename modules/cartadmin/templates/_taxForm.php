<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('cartadmin/'.($form->getObject()->isNew() ? 'taxCreate' : 'taxUpdate').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('cartadmin/taxIndex') ?>">Back to list</a>
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Delete', 'cartadmin/taxDelete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <tr>
        <th><?php echo $form['region_code']->renderLabel() ?></th>
        <td>
          <?php echo $form['region_code']->renderError() ?>
          <?php echo $form['region_code'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['rate']->renderLabel() ?></th>
        <td>
          <?php echo $form['rate']->renderError() ?>
          <?php echo $form['rate'] ?>
        </td>
      </tr>
    </tbody>
  </table>
</form>
