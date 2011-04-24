<?php

/**
 * PluginCategory form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginCategoryForm extends BaseCategoryForm
{
	public function setup()
  {
    $this->setWidgets(array(
      'id'            => new sfWidgetFormInputHidden(),
      'name'          => new sfWidgetFormInputText(),
      'url'           => new sfWidgetFormInputText(),
      'parent'        => new WidgetChoiceNestedSet(array('model' => $this->getRelatedModelName('Category'), 'add_empty' => true)),
      'description'   => new sfWidgetFormInputText(),
      'active'        => new sfWidgetFormInputCheckbox(),
      'sortorder'     => new sfWidgetFormInputText(),
      'slug'          => new sfWidgetFormInputText(),
      'products_list' => new sfWidgetFormDoctrineChoice(array('multiple' => true, 'model' => 'Product')),
    ));

    $this->setValidators(array(
      'id'            => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'          => new sfValidatorString(array('max_length' => 80)),
      'url'           => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'parent'        => new ValidatorChoiceNestedSet(array('model' => $this->getRelatedModelName('Category'), 'node' => $this->getObject(), 'required' => false)),
      'description'   => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'active'        => new sfValidatorBoolean(array('required' => false)),
      'sortorder'     => new sfValidatorInteger(array('required' => false)),
      'slug'          => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'products_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Product', 'required' => false)),
    ));

    $this->setDefault('active', 1);
    
    if ($this->getObject()->getNode()->hasParent()) { $this->setDefault('parent', $this->getObject()->getNode()->getParent()->getId()); }
    
    $this->validatorSchema->setPostValidator(
      new sfValidatorDoctrineUnique(array('model' => 'Category', 'column' => array('slug')))
    );

    $this->widgetSchema->setNameFormat('category[%s]');
    
    $this->widgetSchema->setLabels(array(
      'name'   => 'Category',
      'parent' => 'Parent category',
    ));

    $this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

    $this->setupInheritance();
    
    $this->getValidator('parent')->setMessage('node', 'A category cannot be made a descendent of itself.');
    
    //parent::setup();

  }

  
  
  public function setParent($id) {
     $this->setDefault('parent', $id);
  }
  
  public function doSave($con = null)
  {
    parent::doSave($con);

    if (!$this->getValue('parent')) {
      $treeObject = Doctrine_Core::getTable('Category')->getTree();
      $roots = $treeObject->fetchRoots();
      //If root was deleted, recreate
      if (count($roots) == 0) { 
        $rootCategory = new Category();
        $rootCategory->setName("Categories");
        $rootCategory->setActive(true);
        $rootCategory->save();
        $treeObject->createRoot($rootCategory); 
        $parent = $rootCategory;
      }
      else { $parent = Doctrine::getTable('Category')->findOneById($roots[0]['id']); }
      
      if ($this->isNew()) { $this->getObject()->getNode()->insertAsFirstChildOf($parent); }
      else { $this->getObject()->getNode()->moveAsFirstChildOf($parent); }
    }

    
    elseif ($this->getValue('parent'))
    {
      $parent = Doctrine::getTable('Category')->findOneById($this->getValue('parent'));
      if ($this->isNew()) { $this->getObject()->getNode()->insertAsLastChildOf($parent); }
      else { $this->getObject()->getNode()->moveAsLastChildOf($parent); }
    }
    // if no parent was selected, add/move this node to be a new root in the tree
    /*else
    {
      $categoryTree = Doctrine::getTable('Category')->getTree();
      if ($this->isNew()) { 
        $rootNode = new Category();
        
        $categoryTree->createRoot($this->getObject()); 
      }
      else { $this->getObject()->getNode()->makeRoot($this->getObject()->getId()); }
    }*/
  }



}
