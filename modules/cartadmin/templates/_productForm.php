<?php use_stylesheets_for_form($form) ?>
<?php use_javascripts_for_form($form) ?>
<h1>Edit Product</h1>
<form action="<?php echo url_for('cartadmin/'.($form->getObject()->isNew() ? 'productCreate' : 'productUpdate').(!$form->getObject()->isNew() ? '?id='.$form->getObject()->getId() : '')) ?>" method="post" <?php $form->isMultipart() and print 'enctype="multipart/form-data" ' ?>>
<?php if (!$form->getObject()->isNew()): ?>
<input type="hidden" name="sf_method" value="put" />
<?php endif; ?>
  <table>
    <tfoot>
      <tr>
        <td colspan="2">
          <?php echo $form->renderHiddenFields(false) ?>
          &nbsp;<a href="<?php echo url_for('cartadmin/productIndex') ?>">Back to list</a>
          <?php if (!$form->getObject()->isNew()): ?>
            &nbsp;<?php echo link_to('Delete', 'cartadmin/productDelete?id='.$form->getObject()->getId(), array('method' => 'delete', 'confirm' => 'Are you sure?')) ?>
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
        <th><?php echo $form['code']->renderLabel() ?></th>
        <td>
          <?php echo $form['code']->renderError() ?>
          <?php echo $form['code'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['description']->renderLabel() ?></th>
        <td>
          <?php echo $form['description']->renderError() ?>

          <?php echo $form['description']->render(array('class' => 'ckeditor')) ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['quantity']->renderLabel() ?></th>
        <td>
          <?php echo $form['quantity']->renderError() ?>
          <?php echo $form['quantity'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['price']->renderLabel() ?></th>
        <td>
          <?php echo $form['price']->renderError() ?>
          <?php echo $form['price'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['weight']->renderLabel() ?></th>
        <td>
          <?php echo $form['weight']->renderError() ?>
          <?php echo $form['weight'] ?>
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
        <th><?php echo $form['categories_list']->renderLabel() ?></th>
        <td>
          <?php echo $form['categories_list']->renderError() ?>
          <?php echo $form['categories_list'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['photo_path']->renderLabel() ?></th>
        <td>
          <?php echo $form['photo_path']->renderError() ?>
          <?php echo $form['photo_path'] ?>
        </td>
      </tr>
      <tr>
        <th><?php echo $form['slug']->renderLabel() ?></th>
        <td>
          <?php echo $form['slug']->renderError() ?>
          <?php echo $form['slug'] ?>
        </td>
      </tr>

    </tbody>
  </table>
</form>
