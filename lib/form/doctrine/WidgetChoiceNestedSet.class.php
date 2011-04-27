<?php
class WidgetChoiceNestedSet extends sfWidgetFormDoctrineChoice
{
	public function getChoices()
	{
		$choices = array();
		if (false !== $this->getOption('add_empty'))
		{
			$choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
		}

		if (null === $this->getOption('table_method'))
		{
			$query = null === $this->getOption('query') ? Doctrine_Core::getTable($this->getOption('model'))->createQuery() : $this->getOption('query');
			$query->addOrderBy('root_id asc')
			->addOrderBy('lft asc');
			$objects = $query->execute();
		}
		else
		{
			$tableMethod = $this->getOption('table_method');
			$results = Doctrine_Core::getTable($this->getOption('model'))->$tableMethod();

			if ($results instanceof Doctrine_Query)
			{
				$objects = $results->execute();
			}
			else if ($results instanceof Doctrine_Collection)
			{
				$objects = $results;
			}
			else if ($results instanceof Doctrine_Record)
			{
				$objects = new Doctrine_Collection($this->getOption('model'));
				$objects[] = $results;
			}
			else
			{
				$objects = array();
			}
		}

		$method = $this->getOption('method');
		$keyMethod = $this->getOption('key_method');

		foreach ($objects as $object)
		{
			$choices[$object->$keyMethod()] = str_repeat('&nbsp;', ($object['level'] * 4)) . $object->$method();
		}

		return $choices;
	}
}
?>