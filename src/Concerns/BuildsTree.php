<?php


namespace SimpelDigitaal\TreeModel\Concerns;


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
            return DB::table($this->getTable())->where($this->getParentKeyName(), '=', $parentId)->get($fields);
        };

        $builder = function ($model, $start) use ($getModelList, &$builder) {

            $end = $start + 1;
            $children = $getModelList($model->{$this->getKeyName()});

            if (count($children)) {
                foreach($children as $child) {
                    $end = $builder($child, $end);
                }
            }
            DB::table($this->getTable())->where('id', $model->{$this->getKeyName()})->update([
                $this->getTreeStartName() => $start,
                $this->getTreeEndName() => $end
            ]);
            return ($end + 1);
        };

        $categories = $getModelList();
        $nextStart = 1;
        foreach($categories as $category) {
            $nextStart = $builder($category, $nextStart);
        }
    }

}