<?php

namespace Petrelli\ScopedController;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


/**
 * Quick and oversimplified implementation of Ruby on Rails has_scope gem.
 *
 * Basic functionality:
 *    1 - We setup a base chainable object, usually an Eloquent Query Builder
 *    2 - We define relationships between URL parameters and scopes changing the $scopes variable.
 *
 * That will be it. The controller apply the scopes to the base chain
 * checking that the attribute is present at the url, and if it's present it
 * will execute the scope call on the chain with that value.
 * This way we create an easy way of chaining scopes dynamically.
 *
 */


class BaseController extends Controller
{


    protected $entity;


    // Collection resultset memoization
    protected $collection;


    // Default elements per page
    const PER_PAGE = 20;


    /**
     *
     * Define here the set of rules to apply scopes
     * The key is the expected parameter
     * The value is the scope to be executed on the chain
     *
     * [ parameter => scopeName, .... ]
     *
     */
    protected $scopes = [
    ];

    /**
     *
     * Returns the processed collection.
     * Function added to allow redefinition and add custom scopes at the end
     *
     */
    protected function collection()
    {
        if (!$this->collection)
            $this->collection = $this->endOfAssociationChain();

        return $this->collection;
    }

    /**
     *
     * Returns the chain to be used as a collection
     * Usually a type that responds to query builder behavior
     *
     * Redefine this function if you define an entity different than
     * an eloquent model
     *
     * Example:
     * $entity = \App\Models\Post::class;
     *
     */
    protected function beginOfAssociationChain()
    {
        $model   = new $this->entity;
        $builder = $model->newQuery();

        return $builder;
    }


    /**
     *
     * Returns the chain with all scopes applied to it
     *
     */
    protected function endOfAssociationChain()
    {
        $base = $this->beginOfAssociationChain();

        return $this->applyScopes($base);
    }


    /**
     *
     * Apply all present scopes to the passed builder.
     * It receives the chain as a parameter.
     * Usually just an Eloquent query builder.
     *
     */
    protected function applyScopes($query)
    {
        if (!empty($this->scopes)) {
            foreach ($this->scopes as $parameter => $scope) {
                if (request()->input($parameter) != null) {

                    // If the scope is defined as an array, call the scope
                    // passing all parameters in order
                    if (is_array($scope)) {
                        $scopeName       = $scope[0];
                        $scopeParameters = collect($scope[1])->map(function($field) use ($parameter) {
                            return request()->input($parameter)[$field];
                        })->toArray();

                        $query->$scopeName(...$scopeParameters);
                    } else {
                        $query->$scope(request()->input($parameter));
                    }

                }
            }
        }

        return $query;
    }


    /**
     *
     * Returns a boolean indicating if any scope is present
     *
     */
    protected function hasAnyScope()
    {
        if (empty($this->scopes)) {
            return true;
        } else {
            foreach ($this->scopes as $parameter => $scope) {
                if (request()->input($parameter) != null) {
                    return true;
                }
            }
        }

        return;
    }

}
