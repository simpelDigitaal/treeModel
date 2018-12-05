<?php


namespace SimpelDigitaal\TreeModel;


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class TreeModelServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        Blueprint::macro('tree', function ($prefix = 'tree') {
            $this->unsignedInteger("{$prefix}_start")->nullable()->default(null);
            $this->unsignedInteger("{$prefix}_end")->nullable()->default(null);
        });

        Blueprint::macro('treeWithId', function ($name = 'parent_id', $prefix = 'tree') {
            $this->unsignedInteger($name)->nullable()->default(null);
            $this->tree($prefix);

            $this->foreign($name)->references('id')->on($this->getTable());
        });
        
        Blueprint::macro('dropTree', function ($prefix = 'tree') {
            $this->dropColumn([
                "{$prefix}_start",
                "{$prefix}_end"
            ]);
        });

        Blueprint::macro('dropTreeWithId', function ($name = 'parent_id', $prefix = 'tree') {
            $this->dropForeign([$name]);

            $this->dropColumn([
                $name,
                "{$prefix}_start",
                "{$prefix}_end"
            ]);
        });
    }
}