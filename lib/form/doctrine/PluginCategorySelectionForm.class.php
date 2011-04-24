<?php

/**
 * PluginCategory form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
class PluginCategorySelectionForm extends BaseForm 
{

  function __construct($filter=null)
  {
    parent::__construct();
    $this->setDefault('filter', $filter);
  }
  
	public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'filter'        => new WidgetChoiceNestedSet(array('model' => 'Category', 'add_empty' => true)),
    ));

    $this->widgetSchema->setLabel('filter', 'Filter product by category:');

    
  }





}
