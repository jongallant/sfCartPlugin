<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>

<form action="<?php echo url_for('cartadmin/'.($form->getObject()->isNew() ? 'categoryCreate' : 'categoryUpdate').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('cartadmin/categoryIndex') ?>">Back to list</a>
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Delete', 'cartadmin/categoryDelete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure? (Please note that deleting a category will also delete all of its related child nodes.)')) ?>
          <?php endif; ?>
          <input type="submit" value="Save" />
        </td>
      </tr>
    </tfoot>
    <tbody>
      <?php echo $form->renderGlobalErrors() ?>
      <tr>
        <th><?php echo $form['name']->renderLabel() ?></th>
        <td>
          <?php echo $form['name']->renderError() ?>
          <?php echo $form['name'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['parent']->renderLabel() ?></th>
        <td>
          <?php echo $form['parent']->renderError() ?>
          <?php echo $form['parent'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['description']->renderLabel() ?></th>
        <td>
          <?php echo $form['description']->renderError() ?>
          <?php echo $form['description'] ?>
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
        <th><?php echo $form['products_list']->renderLabel() ?></th>
        <td>
          <?php echo $form['products_list']->renderError() ?>
          <?php echo $form['products_list'] ?>
        </td>
      </tr>

     

    </tbody>
  </table>
</form>
