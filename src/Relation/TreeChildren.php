<?php


namespace SimpelDigitaal\TreeModel\Relation;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class TreeChildren extends Relation
{
    protected $treeStart;
    protected $treeEnd;

    /**
     * Create a new relationship instance with tree.
     *
     * @param Model $parent
     * @param string $treeStart
     * @param string $treeEnd
     */
    public function __construct(Model $parent, $treeStart = 'tree_start', $treeEnd = 'tree_end')
    {
        $this->treeStart = $treeStart;
        $this->treeEnd = $treeEnd;
        $query = $parent->newQuery();
        parent::__construct($query, $parent);
    }


    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        if (static::$constraints) {
            $this->query->where(function (Builder $query) {
                $query->where($this->treeStart, '>', $this->parent->getAttribute($this->treeStart));
                $query->where($this->treeEnd, '<', $this->parent->getAttribute($this->treeEnd));
            });
        }
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        $boundries = $this->getBoundries ($models, $this->treeStart, $this->treeEnd);

        $this->query->where(function (Builder $query) use ($boundries) {
            foreach($boundries as list($lftBoundry, $rgtBoundry)) {
                $query->orWhere(function (Builder $innerQuery) use ($lftBoundry, $rgtBoundry) {
                    $innerQuery->where($this->treeStart, '>', $lftBoundry);
                    $innerQuery->where($this->treeEnd, '<', $rgtBoundry);
                });
            }
        });
    }

    /**
     * Initialize the relation on a set of models.
     *
     * @param  array $models
     * @param  string $relation
     * @return array
     */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->parent->newCollection());
        }

        return $models;
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param  array $models
     * @param  \Illuminate\Database\Eloquent\Collection $results
     * @param  string $relation
     * @return array
     */
    public function match(array $models, Collection $results, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->mapLeaves($model, $results));
        }
        return $models;
    }

    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        return $this->query->get();
    }


    /**
     * We will limit the query with the `tree_start` and `tree_end` boundries of the models;
     *
     * @param array $models
     * @param $treeStart
     * @param $treeEnd
     * @return array
     */
    protected function getBoundries(array $models, $treeStart, $treeEnd)
    {
        // @TODO: discard boundries within boundries: e.g. discard 2..4 if 1..5 is already in place
        return collect($models)->map(function (Model $model) use ($treeStart, $treeEnd) {
            return [$model->getAttribute($treeStart), $model->getAttribute($treeEnd)];
        })->sort(function ($first, $second) {
            if ($first[0] === $second[0]) {
                return 0;
            }
            return $first[0] < $second[0] ? -1 : 1;
        })->all();
    }

    /**
     * After fetching the results, these results need to match their parent.
     * Therefore we will map these `leaves` from the result to the model.
     *
     * @param Model $model
     * @param Collection $results
     * @return Collection
     */
    protected function mapLeaves(Model $model, Collection $results)
    {
        $start = $model->getAttribute($this->treeStart);
        $end = $model->getAttribute($this->treeEnd);

        return $model->newCollection(
            $results
                        ->where($this->treeStart, '>', $start)
                        ->where($this->treeEnd, '<', $end)
                        ->all()
        );
    }
}