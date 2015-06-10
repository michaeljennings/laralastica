# Laralastica

A laravel 5 package that adds the ability to search eloquent models using elasticsearch results, it also handles 
indexing and removing documents when you save or delete models.

- [Installation](#installation)
- [Usage](#usage)
- [Searching](#searching)

## Installation
This package requires at least PHP 5.4 and at present only supports Laravel 5.0.

To install through composer either run `composer require michaeljennings/laralastica` or add the package to you 
composer.json

```php
"michaeljennings/laralastica": "~0.1"
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

  'Carpenter' => 'Michaeljennings\Laralastica\Facades\Laralastica',

);
```

Finally publish the package config using `php artisan vendor:publish`. Once the config has published you can edit the
`config/laralastica.php' file to set your elasticsearch index, host and port.

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
	$query->match('foo', 'bar', 'phrase', false);
});
```