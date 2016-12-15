# Laralastica [![Build Status](https://travis-ci.org/michaeljennings/laralastica.svg?branch=master)](https://travis-ci.org/michaeljennings/laralastica) [![Latest Stable Version](https://poser.pugx.org/michaeljennings/laralastica/v/stable)](https://packagist.org/packages/michaeljennings/laralastica) [![Latest Unstable Version](https://poser.pugx.org/michaeljennings/laralastica/v/unstable)](https://packagist.org/packages/michaeljennings/laralastica) [![Coverage Status](https://coveralls.io/repos/github/michaeljennings/laralastica/badge.svg?branch=develop)](https://coveralls.io/github/michaeljennings/laralastica?branch=develop) [![License](https://poser.pugx.org/michaeljennings/laralastica/license)](https://packagist.org/packages/michaeljennings/laralastica)

A laravel 5 package that adds the ability to search eloquent models using elasticsearch results, it also handles 
indexing and removing documents when you save or delete models.

- [Installation](#installation)
    - [Configuration](#configuration)
- [Usage](#usage)
- [Searching](#searching)
    - [Searching Without the Searchable Trait](#searching-without-the-searchable-trait)
- [Queries](#queries)
- [Paginate Results](#paginate-results)
- [The Result Collection](#the-result-collection)

## Installation
This package requires at least PHP 5.4 and Laravel >=5.0.

To install through composer either run `composer require michaeljennings/laralastica` or add the package to you 
composer.json.

```php
"michaeljennings/laralastica": "~2.0"
```

Then add the laralastica service provider into your providers array in `config/app.php`.

```php
'providers' => array(

	'Michaeljennings\Laralastica\LaralasticaServiceProvider'

);
```

The package also comes with a facade, to use it add it to your aliases array in `config/app.php`.

```php
'aliases' => array(

  'Laralastica' => 'Michaeljennings\Laralastica\Facades\Laralastica',

);
```

### Configuration

Finally publish the package config using `php artisan vendor:publish`. Once the config has published you can edit the
`config/laralastica.php' file to set your elasticsearch connection. To set the connections you can either pass the 
host and port to connect to, or alternatively you can pass a url to connect with.

```php
'drivers' => [
  'elastica' => [
    'hosts' => [
      'connectionStrategy' => 'RoundRobin',
      'connections' => [
        [
          'host' => 'localhost',
          'port' => 9200
        ]
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

### Set Elasticsearch Type

To set the elasticsearch type for the model use the `getSearchType` method to return the name of the type. By default 
this will return the table name the model is using.

```php
public function getSearchType()
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

When you index attributes you may need to type cast the value, to do this use the `getSearchDataTypes` method. This 
must return an array with the key as the column being indexed and the value as the data type. The data types supported 
are:

- int
- string
- float
- bool

```php
public function getSearchDataTypes()
{
	return [
		'price' => 'float',
		'active' => 'bool',
		'quantity' => 'int',
		'name' => 'string'
	];
}
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

	$query->match('foo', 'bar')->must();
	$query->terms('bar', ['baz'])->should();
	$query->wildcard('baz', 'qux*', 1.0)->mustNot();

})->get();
```

You may also chain any Laravel query builder methods before or after searching.

```php
Foo::where('foo', 'bar')->search(function(Builder $query) {

	$query->match('foo', 'bar');

})->orderBy('baz')->get();
```

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

- The type/types you are searching in
- The query to be run

```php
$laralastica->search('foo', function($q) {
	$q->matchAll();
});
```

To search across multiple elasticsearch types simply pass an array of types as the first parameter.

```php
$laralastica->search(['foo', 'bar], function($q) {
	$q->matchAll();
});
```

To get a paginated list of results hit the `paginate` method and the amount to paginate by.

```php
$laralastica->paginate('foo', function($q) {
	$q->matchAll();
}, 15);
```

## Queries

The elasticsearch queries are powered by the great [elastica package](https://github.com/ruflin/Elastica).

There are some preset queries on the query builder, but it is also possible to create an instance of an elastica query and pass that through.
 
### Available Queries

A list of the available queries can be found below. 

Each of the queries can optionally be passed a callback as the final parameter which will allow you to access the raw elastica query.

### Common Query

```php
$laralastica->search('foo', function($query) {

    $query->common('baz', 'qux', 1.0);
    $query->common('baz', 'qux', 1.0, function($commonQuery) {
        $commonQuery->setMinimumShouldMatch(5);
    });
    
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

    $query->match('baz', 'qux');
    $query->match('baz', 'qux', function($matchQuery) {
        $matchQuery->setFieldBoost('foo');
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