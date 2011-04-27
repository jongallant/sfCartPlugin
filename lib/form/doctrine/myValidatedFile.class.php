<?php
class myValidatedFile extends sfValidatedFile
{
  public function generateFilename()
  {
    return $this->getOriginalName();
  }
}
?>