<?php


namespace SimpelDigitaal\TreeModel\Concerns;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait BuildsTree
{
    /**
     * Default Eloquent Model method: Get the primary key for the model.
     *
     * @return string
     */
    abstract function getKeyName();

    /**
     * Get the parent key Name of this model
     *
     * @return mixed
     */
    abstract function getParentKeyName();

    /**
     * Default Eloquent Model method: Get the table associated with the model.
     *
     * @return string
     */
    abstract function getTable();

    /**
     * Fieldname in table of tree start
     *
     * @return string
     */
    abstract function getTreeStartName();

    /**
     * Fieldname in table of tree end
     *
     * @return string
     */
    abstract function getTreeEndName();


    /**
     * Get a new query builder that doesn't have any global scopes or eager loading.
     *
     * @return \Illuminate\Database\Eloquent\Builder|static
     */
    abstract public function newModelQuery();

    /**
     * Builds up a tree on a model.
     */
    final public function buildsTree()
    {
        $fields = [
            $this->getKeyName(),
            $this->getParentKeyName(),
            $this->getTreeStartName(),
            $this->getTreeEndName(),
        ];

        $getModelList = function ($parentId = null) use ($fields) {
            return $this->newModelQuery()->where($this->getParentKeyName(), '=', $parentId)->get($fields);
        };

        $builder = function (Model $model, $start) use ($getModelList, &$builder) {

            $end = $start + 1;
            $children = $getModelList($model->{$this->getKeyName()});

            if (count($children)) {
                foreach($children as $child) {
                    $end = $builder($child, $end);
                }
            }

            $model->setAttribute($this->getTreeStartName(), $start);
            $model->setAttribute($this->getTreeEndName(), $end);
            $model->save();
            return ($end + 1);
        };

        DB::transaction(function () use ($getModelList, $builder) {
            $categories = $getModelList();
            $nextStart = 1;
            foreach($categories as $category) {
                $nextStart = $builder($category, $nextStart);
            }
        });
    }

}