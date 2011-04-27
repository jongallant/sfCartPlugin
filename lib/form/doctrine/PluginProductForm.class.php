<?php

/**
 * PluginProduct form.
 *
 * @package    ##PROJECT_NAME##
 * @subpackage form
 * @author     ##AUTHOR_NAME##
 * @version    SVN: $Id: sfDoctrineFormPluginTemplate.php 23810 2009-11-12 11:07:44Z Kris.Wallsmith $
 */
abstract class PluginProductForm extends BaseProductForm
{
	public function setup()
	{

		$this->setWidgets(array(
      'id'              => new sfWidgetFormInputHidden(),
      'name'            => new sfWidgetFormInputText(),
      'code'            => new sfWidgetFormInputText(),
      'description'     => new WidgetCKEditor(),
      'description2'    => new sfWidgetFormTextarea(),
      'weight'          => new sfWidgetFormInputText(),
      'sortorder'       => new sfWidgetFormInputText(),
      'photo_path'      => new sfWidgetFormInputFile(),
      'slug'            => new sfWidgetFormInputText(),
      'price'           => new sfWidgetFormInputText(),
      'quantity'        => new sfWidgetFormInputText(),
      'active'          => new sfWidgetFormInputCheckbox(),
      'categories_list' => new WidgetChoiceNestedSet(array('model' => $this->getRelatedModelName('Categories'), 'multiple' => true)),
		));

		$this->setValidators(array(
      'id'              => new sfValidatorChoice(array('choices' => array($this->getObject()->get('id')), 'empty_value' => $this->getObject()->get('id'), 'required' => false)),
      'name'            => new sfValidatorString(array('max_length' => 100)),
      'code'            => new sfValidatorString(array('max_length' => 100, 'required' => false)),
      'description'     => new sfValidatorString(array('max_length' => 4000, 'required' => false)),
      'description2'    => new sfValidatorString(array('max_length' => 4000, 'required' => false)),
      'weight'          => new sfValidatorPass(array('required' => false)),
      'sortorder'       => new sfValidatorInteger(array('required' => false)),
      'photo_path'      => new sfValidatorFile(array('validated_file_class' => 'myValidatedFile', 'path' => sfConfig::get('sf_upload_dir').'/sfCart/products', 'required' => false, 'mime_types' => 'web_images')),
      'slug'            => new sfValidatorString(array('max_length' => 255, 'required' => false)),
      'price'           => new sfValidatorNumber(array('required' => false)),
      'quantity'        => new sfValidatorInteger(array('required' => false)),
      'active'          => new sfValidatorBoolean(array('required' => false)),
      'categories_list' => new sfValidatorDoctrineChoice(array('multiple' => true, 'model' => 'Category', 'required' => false)),
		));

		$this->setDefault('active', 1);

		$this->validatorSchema->setPostValidator(
		new sfValidatorDoctrineUnique(array('model' => 'Product', 'column' => array('slug')))
		);

		$this->widgetSchema->setNameFormat('product[%s]');

		$this->errorSchema = new sfValidatorErrorSchema($this->validatorSchema);

		$this->setupInheritance();
	}



	public function save($con = null)
	{
		if (!file_exists(sfConfig::get('sf_upload_dir').'/sfCart/products/')) { mkdir(sfConfig::get('sf_upload_dir').'/sfCart/products/'); }

		$return = parent::save($con);
		$fullpath = $this->getValue('photo_path');
		$genFileName = basename($fullpath);
		 
		$thumbpath1 = sfConfig::get('sf_upload_dir').'/sfCart/products/48x48/'.$genFileName;
		$thumbpath2 = sfConfig::get('sf_upload_dir').'/sfCart/products/172x129/'.$genFileName;
		$thumbpath3 = sfConfig::get('sf_upload_dir').'/sfCart/products/300x200/'.$genFileName;

		if (!file_exists(sfConfig::get('sf_upload_dir').'/sfCart/products/48x48/')) { mkdir(sfConfig::get('sf_upload_dir').'/sfCart/products/48x48/'); }
		if (!file_exists(sfConfig::get('sf_upload_dir').'/sfCart/products/172x129/')) { mkdir(sfConfig::get('sf_upload_dir').'/sfCart/products/172x129/'); }
		if (!file_exists(sfConfig::get('sf_upload_dir').'/sfCart/products/300x200/')) { mkdir(sfConfig::get('sf_upload_dir').'/sfCart/products/300x200/'); }

		//not handling if file already exists
		if (file_exists($fullpath)) {
			$thumbnail = new Thumb(48, 48);
			$thumbnail->loadFile($fullpath);
			$thumbnail->cropImage(48, 48, $fullpath, $thumbpath1);

			$thumbnail = new Thumb(172, 129);
			$thumbnail->loadFile($fullpath);
			$thumbnail->cropImage(172, 129, $fullpath, $thumbpath2);

			$thumbnail = new Thumb(300, 200);
			$thumbnail->loadFile($fullpath);
			$thumbnail->cropImage(300, 200, $fullpath, $thumbpath3);
		}
		return $return;
	}


}
