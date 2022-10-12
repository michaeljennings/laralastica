# Laralastica [![Build Status](https://travis-ci.org/michaeljennings/laralastica.svg?branch=master)](https://travis-ci.org/michaeljennings/laralastica) [![Latest Stable Version](https://poser.pugx.org/michaeljennings/laralastica/v/stable)](https://packagist.org/packages/michaeljennings/laralastica) [![Coverage Status](https://coveralls.io/repos/github/michaeljennings/laralastica/badge.svg?branch=master)](https://coveralls.io/github/michaeljennings/laralastica?branch=master) [![License](https://poser.pugx.org/michaeljennings/laralastica/license)](https://packagist.org/packages/michaeljennings/laralastica)

A laravel package that adds the ability to search eloquent models using elasticsearch results, it also handles
indexing and removing documents when you save or delete models.

- [Installation](#installation)
    - [Configuration](#configuration)
- [Usage](#usage)
- [Building Your Index](#building-your-index)
- [Searching](#searching)
    - [Searching Soft Deleted Records](#searching-soft-deleted-records)
    - [Searching Without the Searchable Trait](#searching-without-the-searchable-trait)
    - [Limit Results](#limit-results)
    - [Offsetting Results](#offsetting-results)
    - [Sorting Results](#sorting-results)
- [Queries](#queries)
- [Filters](#filters)
- [Paginate Results](#paginate-results)
- [The Result Collection](#the-result-collection)

## Upgrading from 3.0 to 4.0

When hitting the `search` method on a model the query builder will return an instance of `Michaeljennings\Laralastica\Eloquent\ResultCollection` instead of `Illuminate\Database\Eloquent\Collection`.

```php
// This will return an instance of Michaeljennings\Laralastica\Eloquent\ResultCollection
Model::search(function(Builder $builder) {
  $builder->matchAll();
})->get();
```

This is useful as you get access to the `totalHits`, `maxScore`, and `totalTime` methods.

However if you want to use the default collection you may override the `newCollection` method on your model and return the collection instance you need.

```php
/**
 * Create a new database notification collection instance.
 *
 * @param array $models
 * @return ResultCollection
 */
public function newCollection(array $models = [])
{
    return new Illuminate\Database\Eloquent\Collection($models);
}
```

## Installation

Check the table below to see which version you will need.

|Laralastica|Laravel|Elasticsearch|PHP
|---|---|---|---|
|4.x|^8.x|7.x|^8.0
|3.x|^5.1|6.x|^7.0
|2.x|^5.1|2.x-5.x|^5.5.9|

To install through composer either run `composer require michaeljennings/laralastica` or add the package to you
composer.json.

```php
"michaeljennings/laralastica": "^4.0"
```

For Laravel 5.5 and upwards, the service provider and facade will be loaded automatically. 

For older versions of Laravel, you will need to add the laralastica service provider into your providers array in `config/app.php`.

```php
'providers' => [

  'Michaeljennings\Laralastica\LaralasticaServiceProvider'

];
```

The package also comes with a facade, to use it add it to your aliases array in `config/app.php`.

```php
'aliases' => [

  'Laralastica' => 'Michaeljennings\Laralastica\Facades\Laralastica',

];
```

### Configuration

Finally publish the package config using `php artisan vendor:publish`. Once the config has published you can edit the `config/laralastica.php' file to set your elasticsearch connection.

The package comes with 2 drivers: elastica, and null. By default the package will use the elastica driver. The null driver is mainly testing purposes where you don't want to have an elasticsearch instance running.

```php
'driver' => 'elastica',
```

As of laralastica 3.0 we now use multiple indexes, rather than multiple types in one index. To see why check this post [about the removal of types](https://www.elastic.co/guide/en/elasticsearch/reference/current/removal-of-types.html).

If you are using multiple environments (production, staging, testing etc.) you can define a index prefix in the config. This will the be added to each index when searcing, adding documents etc.

```php
'index_prefix' => 'testing_',
```

Finally you need to configure your elasticsearch connection. Out of the box the package comes ready to support multiple connections.

However you can pass through any of the parameters the elastica client can receive, check the [elastica documentation](https://github.com/ruflin/Elastica) for more information.

To set the connection you wish to use either enter the host and port you want to connect to, or the url to connect with.

```php
'drivers' => [
  'elastica' => [
    'servers' => [
      [
        'host' => 'localhost',
        'port' => 9200
      ],
      [
        'url' => 'https://user:pass@your-search.com/'
      ]
    ],
  ]
]
```

## Usage

To get started using the package simply add the `Searchable` trait to the models you want to index and search.

```php
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Searchable;

class Foo extends Model
{
	use Searchable;
}
```

Once you have added the trait it will use [model events](http://laravel.com/docs/5.0/eloquent#model-events) to watch
when a model is saved, deleted or restored and will add or delete the elasticsearch document as appropriate.

### Set Elasticsearch Index

To set the elasticsearch index for the model use the `getIndex` method to return the name of the index. By default
this will return the table name the model is using. The index must be lowercase.

```php
public function getIndex()
{
	return 'foo';
}
```

### Set Elasticsearch Key To Index By

To set the value to index the elasticsearch documents by use the 'getSearchKey' method to return the key. By default
this will return the primary key of the model.

```php
public function getSearchKey()
{
	return $this->key;
}
```

### Set the Attributes to Index

To set which attributes should be indexed for the model use the `getIndexableAttributes` method. The attributes must be
returned as an array of key value pairs. By default all of the models attributes are indexed.

```php
public function getIndexableAttributes()
{
	return [
		'foo' => $this->bar,
	];
}
```

### Type Cast Attributes

In elasticsearch each index has a mapping type which will determine how records are indexed, this means that you have to supply each object in the same format. To this we can use the `casts` property that is supplied by laravel.

```php
protected $casts = [
	'price' => 'float',
	'active' => 'bool',
	'quantity' => 'int',
	'name' => 'string'
];
```

Occasionally you may find yourself wanting to cast values slightly differently for your elasticsearch records. To that you can define the `laralasticaCasts` property and this will allow you to override the casts property.

```php
protected $casts = [
	'price' => 'float',
];

protected $laralasticaCasts = [
	'price' => 'string',
];
```

## Building Your Index
If you already have data you in your database and you want to index it you can use the `laralastica:index` artisan command.

### Defining Models To Index
To get started you'll need to set the models you want to index in the indexable section of the `laralastica.php` config file.

By default the package is setup to index the standard `App\User` class.

```php
'indexable' => [
    'users' => \App\User::class,
]
```

To add a new model we need to set a custom key to reference this model by, this will allow us to index just that model if we want to.

Then the value needs to be the fully qualified path of the model class.

For example if we wanted to index a products model we could do:

```php
'indexable' => [
    'users' => \App\User::class,
    'products' => \App\Product::class,
]
```

Occasionally you may want to index relational data, if you have a large database this can take a long time to lazy load the relations. 
To get around this you can specify the relations to bring when indexing.

```php
'indexable' => [
    'users' => \App\User::class,
    'products' => [
        'model' => \App\Product::class,
        'with' => [
            'category' => function($query) {
                $query->with('subcategories');
            }
        ]
    ]
]
```

### Indexing

If you want to index all of the models you have setup then you can run:
```
php artisan laralastica:index
```

To run a specific index run:
```
php artisan laralastica:index [your-index]

// For our example above it would be 
php artisan laralastica:index products 
```

If you are indexing a large amount of data you may want to push it to the queue, you can do so by providing the queue option

```
php artisan laralastica:index --queue
```

## Searching

To run a search use the `search` method. This uses a closure to search the elasticsearch type and then gets the results
and adds a where in query from the results.

The first parameter for the `search` method is a Closure which gets passed an instance of the laralastica query builder.

The second parameter is the column for the where in query, this defaults to 'id'.

The third parameter is a boolean indicating if the query should be ordered by the order of the elasticsearch results.

```php
Foo::search(function(Builder $query) {

	$query->matchAll();

}, 'foo', true)->get();
```

You can also set whether the query must, should or must not match the value you are searching for.

```php
Foo::search(function(Builder $query) {

	$query->matchQuery('foo', 'bar')->must();
	$query->terms('bar', ['baz'])->should();
	$query->wildcard('baz', 'qux*', 1.0)->mustNot();

})->get();
```

You may also chain any Laravel query builder methods before or after searching.

```php
Foo::where('foo', 'bar')->search(function(Builder $query) {

	$query->matchQuery('foo', 'bar');

})->orderBy('baz')->get();
```

### Searching Soft Deleted Records

By default laralastica will delete the elasticsearch record when a model is deleted.

Occasionally you may want to search against your soft deleted records, to do that you implement the `SearchSoftDeletes` trait instead of the `Searchable` trait in your model.

```php
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\SearchSoftDeletes;

class Foo extends Model
{
	use SearchSoftDeletes;
}
```
This adds adds two new methods - `searchWithTrashed` and `searchOnlyTrashed`.

Both methods take the same parameters as the search method, but `searchWithTrashed` will search both soft deleted and non-soft deleted results, and `searchOnlyTrashed` will only get soft deleted results.

To only search for non-soft deleted results just use the `search` method as usual.

### Searching Without the Searchable Trait

It is also possible to use Laralastica without using the searchable trait. To do so you can either dependency inject
the class via its contract, use the provided Facade, or use the `laralastica` helper method.

```php
class Foo
{
	public function __construct(Michaeljennings\Laralastica\Contracts\Laralastica $laralastica)
	{
		$this->laralastica = $laralastica;
	}

	public function foo()
	{
		$laralastica = Laralastica::search();
		$laralastica = laralastica();
	}
}
```

To run a new query use the `search` method. This takes two parameters:

- The index/indices you are searching in
- The query to be run

```php
$laralastica->search('foo', function($query) {
	$query->matchAll();
});
```

To search across multiple elasticsearch indices simply pass an array of indices as the first parameter.

```php
$laralastica->search(['foo', 'bar], function($query) {
	$query->matchAll();
});
```

To get a paginated list of results hit the `paginate` method and the amount to paginate by.

```php
$laralastica->paginate('foo', function($query) {
	$query->matchAll();
}, 15);
```

### Limit Results

By default the results will be limited to the size set in the `laralastica.php` config file. However you can override it by hitting the `size` method.

```php
$laralastica->search('foo', function($query) {
	$query->size(50);
});
```

### Offsetting Results

It is also possible to provide an offset by hitting the `from` method.

```php
$laralastica->search('foo', function($query) {
	$query->from(10);
});
```

### Sorting Results

By default we sort the results by their score in descending order. You can override this by hitting the sort method.

```php
$laralastica->search('foo', function($query) {
    // Sort by id in ascending order
    $query->sort('id');
    // Sort by id in descending order
    $query->sort('id', 'desc');
    // Sort by multiple fields
    $query->sort([
        '_score',
        'id' => 'desc'
    ]);
});
```

## Queries

The elasticsearch queries are powered by the great [elastica package](https://github.com/ruflin/Elastica).

There are some preset queries on the query builder, but it is also possible to create an instance of an elastica query and pass that through.

### Available Queries

A list of the available queries can be found below.

Each of the queries can optionally be passed a callback as the final parameter which will allow you to access the raw elastica query.

### Bool Query

```php
$laralastica->search('foo', function($query) {

    $query->bool(function($query) {
        $query->matchQuery('foo', 'bar');
    });

});
```

### Common Query

```php
$laralastica->search('foo', function($query) {

    $query->common('baz', 'qux', 1.0);
    $query->common('baz', 'qux', 1.0, function($commonQuery) {
        $commonQuery->setMinimumShouldMatch(5);
    });

});
```

### Exists Query

```php
$laralastica->search('foo', function($query) {

    $query->exists('baz');

});
```

### Fuzzy Query

```php
$laralastica->search('foo', function($query) {

    $query->fuzzy('baz', 'qux');
    $query->fuzzy('baz', 'qux', function($fuzzyQuery) {
        $fuzzyQuery->setFieldOption('fuzziness', 2);
    });

});
```

### Match Query

```php
$laralastica->search('foo', function($query) {

    $query->matchQuery('baz', 'qux');
    $query->matchQuery('baz', 'qux', function($query) {
        $query->setFieldBoost('foo');
    });

});
```

### Match All Query

```php
$laralastica->search('foo', function($query) {

    $query->matchAll();

});
```

### Query String Query

```php
$laralastica->search('foo', function($query) {

    $query->queryString('testing');
    $query->queryString('testing', function($queryStringQuery) {
        $queryStringQuery->setDefaultField('foo');
    });

});
```

### Range Query

```php
$laralastica->search('foo', function($query) {

    $query->queryString('foo', ['gte' => 1, 'lte' => 20]);
    $query->queryString('foo', ['gte' => 1, 'lte' => 20], function($rangeQuery) {
        $rangeQuery->setParam('foo', ['gte' => 1, 'lte' => 20, 'boost' => 1]);
    });

});
```

### Regular Expression Query

```php
$laralastica->search('foo', function($query) {

    $query->regexp('foo', 'testing');

});
```

### Term Query

```php
$laralastica->search('foo', function($query) {

    $query->term(['foo' => 'bar']);
    $query->term(['foo' => 'bar'], function($termQuery) {
        $termQuery->setTerm('baz', 'qux', 2.0);
    });

});
```

### Terms Query

```php
$laralastica->search('foo', function($query) {

    $query->terms('foo', ['bar', 'baz']);
    $query->terms('foo', ['bar', 'baz'], function($query) {
        $query->setMinimumMatch(5);
    });

});
```

### Wildcard Query

```php
$laralastica->search('foo', function($query) {

    $query->wildcard('foo', 'bar');

});
```

## Filters

Occasionally you will want to run a query to exclude/include records, but you don't want the query to effect the score.

You can do this using filters.

To add a filter hit the `filter` methods and pass it a callback. The callback will be passed an instance of the laralastica builder as the first parameter.

For example if only wanted to search against records that had a due date we could do the following.

```php
$laralastica->search('foo', function($query) {
    $query->matchAll()
          ->filter(function($query) {
            $query->exists('due_date');
          });
});
```

## Paginate Results

To get a paginated list of results use the `paginate` method and pass the amount to paginate the results by.

```php
$laralastica->paginate('foo', function($query) {

    $query->matchAll();

}, 15);
```

## Raw Elastica Queries

To run a raw elastica query create the query instance and then pass it to the `query` method.

```php
$laralastica->search('foo', function($query) {

    $match = new \Elastica\Query\Match();

    $query->query($match);

});
```

## The Result Collection

The search method will return an instance of the result collection. This extends the default laravel collection but also adds a couple of laralastica specific methods.

### Total Hits

Gets the total amount of hits matched by the query.

```php
$results = $laralastica->search('foo', function($query) { $query->matchAll() });

$results->totalHits();
```

### Maximum Score

Gets the maximum score matched by the search.

```php
$results = $laralastica->search('foo', function($query) { $query->matchAll() });

$results->maxScore();
```

### Time Taken

Gets the time taken to execute the elasticsearch query.

```php
$results = $laralastica->search('foo', function($query) { $query->matchAll() });

$results->totalTime();
```
