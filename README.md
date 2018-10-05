# Filterable

This trait allows you to easily create filterable models.

```cli
composer require lukesnowden/filterable:dev-master
```

## Walk-through

Here is a default setup for a filterable class, this one will allow you to filter the results of the customers;

```php
<?php

namespace App\Customers\Filters;

use Lukesnowden\Filterable\Traits\Filterable;
use App\Customers\Filters\OrderByd;
use App\Customers\Filters\Methods;

class FilterCustomers
{

    use Filterable;

    /**
     * @var string
     */
    protected static $defaultOrderBy = 'forename';

    /**
     * Called just before filtering to set local filters
     * @return void
     */
    public function preFilter()
    {
        self::addOrderBys( new OrderBys() );
        self::addFilters( new Methods() );
    }
    
}
```

By default, the order by method is `forename`. We have also setup some default `orderBys` and `filters`. 
Below shows only one `ordderBy` and one `filter` method for forename.

```php
<?php

namespace App\Customers\Filters;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class OrderBys
{

    /**
     * @return Closure
     */
    public function forename() : Closure
    {
        return function( Builder $builder, $order )
        {
            $builder->orderBy( 'forename', $order );
        };
    }
    
}

Class Methods
{
    
    /**
     * @return Closure
     */
    public function forename() : Closure
    {
        return function( Builder $builder, Request $request )
        {
            if( $forename = $request->input( 'forename' ) ) {
                $builder->where( 'forename', 'like', "%{$forename}%" );
            }
        };
    }
    
}
```

We can now us this to order our Customer model;

```php
<?php 

$customers = Customer::active();

( new FilterCustomers( $customers, request() ) )->filter();

$customers = $customers->paginate( 20 );
```

You can also add new `ordderBy` and one `filter` methods;

```php
<?php

FilterCustomers::addFilter( 'emailAddress', function( Builder $builder, Request $request ) 
{
    if( $email = $request->input( 'email' ) ) {
        $builder->where( 'email', 'like', "%{$email}%" );
    }
});
```
