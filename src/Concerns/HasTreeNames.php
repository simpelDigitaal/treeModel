<?php


namespace SimpelDigitaal\TreeModel\Concerns;


trait HasTreeNames
{
    /**
     * Get the current prefix for the tree-fields. Default: 'tree'
     *
     * @return string
     */
    protected function getPrefix(): string
    {
        return property_exists($this, 'prefix') ?
            $this->prefix :
            'tree';
    }

    /**
     * Fieldname in table of tree start
     *
     * @return string
     */
    final protected function getTreeStartName()
    {
        return $this->getPrefix().'_start';
    }


    /**
     * Fieldname in table of tree end
     *
     * @return string
     */
    final protected function getTreeEndName()
    {
        return $this->getPrefix().'_end';
    }

    /**
     * Name of the parent_key for parent-child relationship
     *
     * @return string
     */
    protected function getParentKeyName()
    {
        return property_exists($this, 'parentKeyName') && isset($this->parentKeyName) ?
            $this->parentKeyName :
            'parent_id';
    }


}