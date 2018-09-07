# ScopedController

ScopedController is a Laravel package that will allows you to execute scopes over an Eloquent model depending on the parameters you are receiving on the URL.

Inspired on the Ruby on Rails gem [Has Scope](https://github.com/plataformatec/has_scope).


# Usage

Let's imagine we have a `Book` model and we want to create filter them.

1. Create your controller and be sure to inherit from `Petrelli\ScopedController\BaseController`.

2. Define the `$scopes` variable as an Array following the pattern `[ URLparameter => scopeName, .... ]`

```php
// Here as an example we have two scopes: year and author.
// They will be called if we receive the parameters
// byYear and byAuthor respectively.
protected $scopes = [
    'byYear'   => 'year',
    'byAuthor' => 'author',
];
```

3. Define your $entity variable

```php
protected $entity = Book::class;
```

4. Call your collection and get everything or paginate as you would normally do:

```php
$items = $this->collection()->get();
$items = $this->collection()->paginate(static::PER_PAGE);

```

This setup will work as the following:

```
# Get books filtered by year = 2018
/books?byYear=2018

# Get books filtered by author = cortazar
/books?byAuthor=cortazar

# Get books filtered by year and author
/books?byYear=1961&byAuthor=borges

```

# Extra functionality

## Redefine the chain

If you want to be more specific where and how to start executing your scopes you can always redefine your `beginOfAssociationChain()` function.


For example:


```php
protected function beginOfAssociationChain()
    {
        return Book::published()->where('library', 'NYC');
    }
```

Here your chain will always execute the `published()` scope before any element of the chain, and also will filter books showing only the ones at the NYC library.

Then of course just get or paginate as normal:

```php
$items = $this->collection()->get();
$items = $this->collection()->paginate(static::PER_PAGE);

```

## Applying scopes manually

You can always apply all scopes manually to be more clear in your code:

```php

// Controller function
public function index()
    {
        $items = $this->applyScopes(Book::query())->get();
    }

```

## Check if there's a filter present

Usually for SEO you want to check if you have any filter present. We provide a simple function for this:

```php

// Returns true/false
$this->hasAnyScope()

```


# License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
