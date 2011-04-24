<?php
class WidgetCKEditor extends sfWidgetFormInput
{

    public function render($name, $value = null, $attributes = array(), $errors = array())
 	  {
 	    return '<textarea class="ckeditor" id="product_description" name="product[description]" cols="40" rows="8">' . $value . '</textarea>';

      //$this->renderTag('textarea', array_merge(array('type' => $this->getOption('type'), 'name' => $name, 'value' => $value, 'class' => 'ckeditor'), array()));
 	  }

    
}
?>