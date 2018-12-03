<?php


namespace SimpelDigitaal\TreeModel\Concerns;


trait HasTreeAttributes
{
    abstract protected function getTreeStartName();

    abstract protected function getTreeEndName();

    abstract protected function getAttributeFromArray($key);


    final public function getTreeStartAttribute()
    {
        return $this->getAttributeFromArray($this->getTreeStartName());
    }

    final public function getTreeEndAttribute()
    {
        return $this->getAttributeFromArray($this->getTreeEndName());
    }


}