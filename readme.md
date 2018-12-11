# TreeModel
## Use Cases

A TreeModel, or **nested subset**, is especially usefull for storing and retreiving the complete *tree* of subsequent models. It will prevent many queries while building the layout, like:

* level 1 - A
  * level 2 - AA
    * level 3 - AAA
    * level 3 - AAB
      * level 4 - AABA
      * ...
  * level 2 - AB
  * ...
* ...


Examples for use is a menu for pages or categories.

It could also be used to count all underlying related models, for example all `products` in a category and underlying categories:

```php
$allCategories = $category->allChildren()->select(id);

$productCount = Product::whereHas('categories', function ($query) use ($allCategories, $category) {
    $query->whereIn('id', $allCategories);
    $query->orWhere('id', '=', $category->id);
})->count();

```
*(note: this is just pseudocode, propably not working)*


## Using TreeModel
This package has 2 core-functionalities: the migration-helper and a relation to be included inside an Eloquent Model. The migration-helper will be included using the TreeModelServiceProvider, however, the relation will also work without it.

### Install
Require this package with composer.
```shell
composer require simpeldigitaal/tree-model
```

If you don't use auto-discovery, add the ServiceProvider to the providers array in config/app.php

```php
SimpelDigitaal\TreeModel\TreeModelServiceProvider::class
```

### Use

#### Inside your migration:

**up**
```php
    public function up()
    {
        Schema::table('menu', function (Blueprint $table) {
            ...
            $table->tree();
            ...
        });
    
    
        Schema::table('categories', function (Blueprint $table) {
            ...
            $table->treeWithId();
            ...
        });
    }
```

`$table->tree($prefix = 'tree');`
Generates the necessary fields for storing the tree (*tree_start* and *tree_end*). You are able to change the default prefix.


`$table->treeWithId($name = 'parent_id', $prefix = 'tree');`
Generates besides the necessary fields for storing the tree the parent-child relation too. This field may be used in a `HasMany` or `BelongsTo` relation.


**and down**
```php
    public function down()
    {
        Schema::table('menu', function (Blueprint $table) {
            ...
            $table->dropTree();
            ...
        });
            
        Schema::table('categories', function (Blueprint $table) {
            ...
            $table->dropTreeWithId();
            ...
        });

    }    
```

`$table->dropTree($prefix = 'tree');` Drops the fields for the tree.

`$table->dropTreeWithId($name = 'parent_id', $prefix = 'tree');` Drops the field for the parent-child relation too...


#### Inside your model:
Include the `SimpelDigitaal\WebTree\Concerns\HasTree`-trait to your Eloquent-Model.

On top:
```php
use SimpelDigitaal\WebTree\Concerns\HasTree;

```

Inside your model:
```php
class Categories extends Model {

    use HasTree
    ...
    public function allChildren()
    {
        return $this->getTree();
    }

    public function childrenFromTree()
    {
        return $this->getSubsetTree('allChildren');
    }
    ...
}

```

`getTree` will fetch all children from the database.
`childrenFromTree` will fetch the subset for the next level, seeding all children with their own subset. It will use and it will set the relation specified as first parameter.

#### Building the tree.

After storing a new model or changing an existing model, you have to build the tree again using `$model->buildsTree()`;


#### Building layout:

Example:
```php
    $categories = Category::whereNull('parent_id')->with('allChildren')->get();
```


```blade
<ul>
    @foreach($categories as $category)
        <li>
            {{ $category->name }} | {{ $category->treeStart }} - {{ $category->treeEnd }}
            @include('categories._list', ['categories' => $category->childrenFromTree()])
        </li>
    @endforeach
</ul>
```
Builds with only two queries: first to retreive $categories, second for eagerloading relation 'allChildren'

### Enjoy


## Tips
### Adding constraints
If you want to add a constraint to your tree (for example a `site_id`), you are able to set this constraint using a [global scope](https://laravel.com/docs/5.7/eloquent#global-scopes).

## Sources
* [Inspired by the 'nested set' of the Joomla-framework](https://docs.joomla.org/Using_nested_sets)
  * [Especially this article, from Gijs Van Tulder](https://www.sitepoint.com/hierarchical-data-database/)
  * [Wikipedia about this 'Tree'](https://en.wikipedia.org/wiki/Nested_set_model)
