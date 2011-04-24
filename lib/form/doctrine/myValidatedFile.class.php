<?php
class myValidatedFile extends sfValidatedFile
{
  public function generateFilename()
  {
    return $this->getOriginalName();
  }

	
	//private $savedFilename = "test";
	
  //public function save($file = null, $fileMode = 0666, $create = true, $dirMode = 0777) {
  //  if ($this->savedFilename === null) $this->savedFilename = $file;
  //  return parent::save($this->savedFilename, $fileMode, $create, $dirMode);
  //}

}
?>