# Laralastica [![Build Status](https://travis-ci.org/michaeljennings/laralastica.svg?branch=master)](https://travis-ci.org/michaeljennings/laralastica) [![Latest Stable Version](https://poser.pugx.org/michaeljennings/laralastica/v/stable)](https://packagist.org/packages/michaeljennings/laralastica) [![Latest Unstable Version](https://poser.pugx.org/michaeljennings/laralastica/v/unstable)](https://packagist.org/packages/michaeljennings/laralastica) [![License](https://poser.pugx.org/michaeljennings/laralastica/license)](https://packagist.org/packages/michaeljennings/laralastica)

A laravel 5 package that adds the ability to search eloquent models using elasticsearch results, it also handles 
indexing and removing documents when you save or delete models.

- [Installation](#installation)
- [Usage](#usage)
- [Searching](#searching)
	- [Match Query](#match-query)
	- [Multi Match Query](#multi-match-query)
	- [Match All Query](#multi-all-query)
	- [Fuzzy Query](#fuzzy-query)
	- [Common Query](#common-query)
	- [Range Query](#range-query)
	- [Prefix Query](#prefix-query)
	- [Regular Expression Query](#regular-expression-query)
	- [Term Query](#term-query)
	- [Terms Query](#terms-query)
	- [Wildcard Query](#wildcard-query)

## Installation
This package requires at least PHP 5.4 and at present only supports Laravel 5.0.

To install through composer either run `composer require michaeljennings/laralastica` or add the package to you 
composer.json

```php
"michaeljennings/laralastica": "1.2.*"
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

### Config

Finally publish the package config using `php artisan vendor:publish`. Once the config has published you can edit the
`config/laralastica.php' file to set your elasticsearch index, host and port.

```php
'index' => 'yourindex',
'host' => 'localhost',
'port' => 9200,
```

There is also an empty types array. This allows you to bind an elasticsearch type to a model so that when you search 
elasticsearch it will return results belonging to that type as the specified model.

```php
'types' => [
	'testType' => 'App\TestType'
]
```

## Usage

To get started using the package simply add the `Searchable` trait to the models you want to index and search.

```php
use Illuminate\Database\Eloquent\Model;
use Michaeljennings\Laralastica\Searchable;

class Foo extends Model {
	
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

The second paramater is the column for the where in query, this defaults to 'id'.

The third parameter is a key to group the elasticsearch results, the grouped results are then passed to the where in 
query. This also defaults 'id'.

```php
Foo::search(function(Builder $query) {

	$query->matchAll();

}, 'foo', 'bar')->get();
```

You can also set whether the query must, should or must not match the value you are searching for.

```php
Foo::search(function(Builder $query) {

	$query->match('foo', 'bar')->must();
	$query->term('bar', 'baz')->should();
	$query->wildcard('baz', 'qux*')->mustNot();

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
the class via its contract or use the provided Facade.

```php
class Foo {
	public function __construct(Michaeljennings\Laralastica\Contracts\Wrapper $laralastica)
	{
		$this->laralastica = $laralastica;
	}

	public function foo()
	{
		$laralastica = Laralastica::search();
		$laralastica = app('laralastica');
	}
}
}
```

To run a new query use the `search` method. This takes two parameters:

- The type/types you are searching in
- The query to be run

```php
$laralastica->search('foo', function($q)
{
	$q->matchAll();
});
```

To search across multiple elasticsearch types simply pass an array of types as the first parameter.

```php
$laralastica->search(['foo', 'bar], function($q)
{
	$q->matchAll();
});
```

### Match Query

To run a match query call `match` on the query builder. This takes 4 parameters:

- The column to search
- The query to search for
- The type of search, defaults to phrase
- A flag for if the search should be fuzzy, by default this is false

The two types of search you can run are `phrase` and `phrase_prefix`. The phrase match analyzes the text and creates a 
phrase query out of the analyzed text. The phrase prefix match is the same as phrase, except that it allows for prefix 
matches on the last term in the text.

For more information about the search types [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-match-query.html).

```php
Foo::search(function(Builder $query)
{
	$query->match('foo', 'bar');
	$query->match('foo', 'bar', 'phrase', false);
});
```

### Multi Match Query

To run a multi match query use the `multiMatch` method on the query builder. This takes 6 parameters:

- An array of columns to search in
- The query string to search for
- The type of search, defaults to phrase
- A flag for if the search should be fuzzy, by default this is false
- The tie breaker value, only used with the best_fields type, defaults to 0.0
- An operator, only needed for the cross_fields type, defaults to 'and'

There are 5 different search types for the multi match: best_fields, most_fields, cross_fields, phrase and 
phrase_prefix.

best_fields finds documents which match any field, but uses the _score from the best field.

most_fields finds documents which match any field and combines the _score from each field.

cross_fields treats fields with the same analyzer as though they were one big field. Looks for each word in any field.

phrase runs a match_phrase query on each field and combines the _score from each field.

phrase_prefix runs a match_phrase_prefix query on each field and combines the _score from each field.

For more information about the search types [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-multi-match-query.html).

```php
Foo::search(function(Builder $query)
{
	$query->multiMatch(['foo', 'bar'], 'The Quick Brown Fox');
	$query->multiMatch(['foo', 'bar'], 'The Quick Brown Fox', 'phrase', true);
	$query->multiMatch(['foo', 'bar'], 'The Quick Brown Fox', 'best_fields', true, 0.5);
	$query->multiMatch(['foo', 'bar'], 'The Quick Brown Fox', 'cross_fields', true, 0.0, 'or');
});
```

### Match All Query

To use a match all query use the `matchAll` method on the query builder.

```php
Foo::search(function(Builder $query)
{
	$query->matchAll();
})
```

### Fuzzy Query

To run a fuzzy query use the `fuzzy` method on the query builder. This takes 5 parameters:

- The column to search
- The query string to search for
- The fuzziness value, can be 0, 1, 2 or 'AUTO'. Default to 'AUTO'.
- The prefix length, defaults to 0
- The maximum expansions to allow, defaults to 50.

For more information about the fuzzy query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-fuzzy-query.html).

```php
Foo::search(function(Builder $query)
{
	$query->fuzzy('foo', 'bar');
	$query->fuzzy('foo', 'bar', 1, 2, 100);
});
```

### Common Query

To run a common query use the `common` method on the query builder. This takes 4 parameters:

- The column to search
- The query string to search for
- The cut off, defaults to 0.001
- A flag stating if a minimum match should be allowed, defaults to false

For more information about the common query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-common-terms-query.html).

```php
Foo::search(function(Builder $query)
{
	$query->common('foo', 'bar');
	$query->common('foo', 'bar', 0.001-, true);
})
```

### Range Query

To run a range query use the `range` method on the query builder. This takes 4 parameters:

- The column to search in
- The range to search in
- If you are searching for a date you can specify a timezone
- If you are searching for dates you can specify a date format.

To specify a range you pass an array which gt, gte, lt or lte as keys. So to get any values greater than 3 and less than
10 you would do the following.

```php
Foo::search(function(Builder $query)
{
	$range = [
		'gt' => 3,
		'lt' => 10
	];

	$query->range('foo', $range);
});
```

To search for dates between the 1st January 1970 and 31st January 1970 you would do the following.

```php
Foo::search(function(Builder $query)
{
	$range => [
		'gte' => '1970-01-01',
		'lte' => '1970-01-31'
	];

	$query->range('foo', $range, '+1:00', 'yyyy-mm-dd');
});
```

### Prefix Query

To run a prefix query use the `prefix` method. This takes 2 parameters:

- The column to search in
- The prefix to search for

For more information on the prefix query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-prefix-query.html).

```php
Foo::search(function(Builder $query)
{
    $query->prefix('foo', 'ba');
});
```

### Regular Expression Query

To run a regular expression query use the `regexp` method. This takes 2 parameters:

- The column being searched
- The regular expression to search for

For more information about the term query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-regexp-query.html).

```php
Foo::search(function(Builder $query)
{
    $query->regexp('foo', 'b.*r');
});
```


### Term query

To run a term query use the `term` method. This takes 3 parameters:

- The column to search
- The term to search for
- Set the boost to search by, defaults to 1.0

For more information about the term query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-term-query.html).

```php
Foo::search(function(Builder $query)
{
    $query->term('foo', 'bar', 2.0);
});
```

### Terms Query

To run a terms query use the `terms` method. This takes 3 parameters:

- The column to search in
- An array of terms
- The minimum amount of terms to match, defaults to false

For more information about the terms query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-terms-query.html).

```php
Foo::search(function(Builder $query)
{
    $query->terms('foo', ['foo', 'bar', 'baz'], 2);
});
```

### Wildcard Query 

To run a wildcard query use the `wildcard` method. This takes 2 parameters:

- The column being searched
- The value to search for

For more information about the wildcard query [click here](https://www.elastic.co/guide/en/elasticsearch/reference/current/query-dsl-wildcard-query.html).

```php
Foo::search(function(Builder $query)
{
    $query->wildcard('foo', 'ba*');
});
```