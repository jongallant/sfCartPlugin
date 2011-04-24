<form action="<?php echo url_for('cartadmin/productIndex') ?>" method="post">
<?php echo $form->renderHiddenFields(false) ?>
<?php echo $form['filter']->renderLabel() ?>
<?php echo $form['filter'] ?>
 <input type="submit" value="Apply" />
</form>
