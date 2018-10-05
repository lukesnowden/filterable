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

$customers = Customer::query();

( new FilterCustomers( $customers, request() ) )->filter();

$customers = $customers->paginate( 20 );
```

You can also add new `ordderBy` and `filter` methods;

```php
<?php

FilterCustomers::addFilter( 'emailAddress', function( Builder $builder, Request $request ) 
{
    if( $email = $request->input( 'email' ) ) {
        $builder->where( 'email', 'like', "%{$email}%" );
    }
});

FilterCustomers::addOrderBy( 'emailAddress', function( Builder $builder, $order ) 
{
    $builder->orderBy( 'email', $order );
});
```


#### MIT License

##### Copyright (c) 2018 Luke Snowden

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
