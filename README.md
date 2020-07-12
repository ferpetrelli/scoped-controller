# Scoped Controller

Scoped Controller is a very simple Laravel package (<80 LOC) that will allow you to build queries and cleanup your controllers by executing scopes based on request parameters.

Inspired on the Ruby on Rails gem [Has Scope](https://github.com/plataformatec/has_scope).

# Install

Just include it in your composer.json file

```
composer require petrelli/scoped-controller
```

Or add:

```
"petrelli/scoped-controller": "0.9.*"
```

And run `composer update`.


# Basic Usage

Imagine we have a `Book` model with two filters, by year and by author.

URL parameters might look like the following:

```
# Get books filtered by year == 2020
/books?byYear=2020

# Get books filtered by author == cortazar
/books?byAuthor=cortazar

# Get books filtered by year and author
/books?byYear=1961&byAuthor=borges

```

## Steps

1. Create your controller and be sure to inherit from `Petrelli\ScopedController\BaseController`.

2. Define which class will hold your collection with the `$entity` variable. Usually an Eloquent Model, but could be any class that respond to scopes.

```php
protected $entity = Book::class;
```

3. Now define the `$scopes` variable as an Array following the pattern `[ URLparameter => scopeName, .... ]`

```php

use Petrelli\ScopedController\BaseController;

class EventsController extends BaseController {

// Here as an example we have two scopes: year and author.
// They will be called if we receive the parameters
// byYear and byAuthor respectively.
protected $scopes = [
    'byYear'   => 'year',
    'byAuthor' => 'author',
];

}
```

4. Now to get a filtered collection simply call `$this->collection()`.
You can use any available function. If using Eloquent, you could use `get()`, `paginate(...)`, or anything you need to chain.

```php
$items = $this->collection()->get();
$items = $this->collection()->paginate(static::PER_PAGE);

```

# Customizing the scopes chain

We provide a controller function named  `beginOfAssociationChain()` that you could overload.
In there we build the base query over which we will apply all of our scopes.


For example:


```php
protected function beginOfAssociationChain()
{
    return Book::published()->where('library', 'NYC');
}
```

Here every time we call `$this->collection()` as previously described we will be executing the `published()` scope, and also filtering books where 'library' is 'NYC'.


```php
$items = $this->collection()->get();
$items = $this->collection()->paginate(static::PER_PAGE);

```

# Applying scopes manually

We provide the function `applyScopes($query)` in case you want to manually apply your scopes to a query. As always, which scope will be triggered is a function of the request parameters.

```php

// Controller function
public function index()
{
    $items = $this->applyScopes(Book::query())->get();
}

```


# Extra functionality

## Scopes with multi-value parameters

If you need to define a multi-value parameter just pass it as an array and define the scopes as the following:


```php
// Here as an example we have two scopes: year and author.
// They will be called if we receive the parameters
// byYear and byAuthor respectively.
protected $scopes = [
    'byYear'   => 'year',
    'byAuthor' => 'author',
    'sortBy'   => ['sort_by' ['field', 'direction']],
];
```

Defining the scope as an array will allow you to pass multiple parameters to it coming from the URL as arrays.


## Multi-value scopes in action


```
# Get books filtered by year = 2018 and sorted by author in alphabetical order
/books?byYear=2018&sortBy['field']=author&sortBy['direction']=asc

# Get books filtered by author = borges and sorted by date
/books?byAuthor=borges&sortBy['field']=date&sortBy['direction']=desc
```

You get the idea. The scope is nothing but a simple two parameter element.

```
public function scopeSortBy($query, $value, $direction = 'asc')
{
    //...
}
```

Of course you can generalize to use any number of parameters. Simply add it to the $scopes definition.


## Check if any filter is present

Common use case, if you need to check if any scope is present:

```php

// Returns true/false
$this->hasAnyScope()

```

# Use specific scopes for different actions

Because `$this->collection()` is returning a Query Builder (when using Eloquent for example), you could keep chaining methods and scopes as you would normally do:


```php

public function index()
{
    // Return Books triggering defined scopes
    $items = $this->collection()->get();
}

public function indexBestSellers()
{
    // Return Books triggering defined scopes + bestSeller scope
    $items = $this->collection()->bestSeller()->get();
}
```


# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
