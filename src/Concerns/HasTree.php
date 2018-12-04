<?php


namespace SimpelDigitaal\TreeModel\Concerns;

use Illuminate\Database\Eloquent\Collection;
use SimpelDigitaal\TreeModel\Relation\TreeChildren;

trait HasTree
{
    use HasTreeNames, HasTreeAttributes, BuildsTree;

    abstract public function getRelationValue($key);

    protected function getTree()
    {
        return new TreeChildren($this, $this->getTreeStartName(), $this->getTreeEndName());
    }

    protected function getOrderedTree()
    {
        return $this->getTree()->orderBy($this->getTreeStartName());
    }

    protected function getSubsetTree($relation = 'allChildren', $parentId = 'parent_id', $ownId = 'id')
    {
        $models = $this->getRelationValue($relation);

        if ($models instanceof Collection) {
            $models->each(function ($model) use ($models, $relation) {
                $model->setRelation(
                    $relation,
                    $this->newCollection(
                        $models
                            ->where($this->getTreeStartName(), '>', $model->getTreeStartAttribute())
                            ->where($this->getTreeEndName(), '<', $model->getTreeEndAttribute())
                            ->all()
                    )
                );

            });

            return $models->where($parentId, $this->getAttributeFromArray($ownId));
        }
        // @FIXME: make a specific exception to catch...
        throw new \Exception('Relation is not a collection');
    }
}