<?php 

class ValidatorChoiceNestedSet extends sfValidatorBase
{
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addRequiredOption('node');
    $this->addMessage('node', 'A node cannot be set as a child of itself.');
  }

  protected function doClean($value)
  {
    if (isset($value) && !$value)
    {
      unset($value);
    }
    else
    {
      $targetNode = Doctrine::getTable($this->getOption('model'))->find($value)->getNode();
      if ($targetNode->isDescendantOfOrEqualTo($this->getOption('node')))
      {
        throw new sfValidatorError($this, 'node', array('value' => $value));
      }
      return $value;
    }
  }
}
?>