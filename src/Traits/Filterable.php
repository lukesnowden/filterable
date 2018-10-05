<?php

namespace Lukesnowden\Filterable\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

trait Filterable
{

    /**
     * @var array
     */
    protected static $filters = [];

    /**
     * @var array
     */
    protected static $orderBys = [];

    /**
     * @var string
     */
    protected static $order = 'asc';

    /**
     * @var Builder|null
     */
    protected $builder = null;

    /**
     * @var null
     */
    protected $request = null;

    /**
     * Filterable constructor.
     * @param Builder $builder
     * @param $request
     */
    public function __construct( Builder $builder, $request )
    {
        $this->builder = $builder;
        $this->request = $request;
        return $this;
    }

    /**
     * @param $order
     */
    public static function order( $order )
    {
        if( in_array( strtolower( $order ), [ 'asc', 'desc' ] ) ) {
            self::$order = $order;
        }
    }

    /**
     * @param $class
     */
    public static function addOrderBys( $class )
    {
        try {
            $methods = ( new ReflectionClass( $class ) )->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED );
            foreach( $methods as $method ) {
                $method->setAccessible( true );
                self::addOrderBy( $method->name, $method->invoke( $class ) );
            }
        } catch( ReflectionException $e ) {}
    }

    /**
     * Applies filters
     * @return void;
     */
    public function filter()
    {
        $this->preFilter();
        $this->orderBy();
        foreach( self::$filters as $filter ) {
            $filter( $this->builder, $this->request );
        }
    }

    /**
     * @return void
     */
    protected function orderBy()
    {
        $default = self::$defaultOrderBy;
        self::addFilter( 'order_by', function( Builder $builder, Request $request ) use( $default )
        {
            $orderBy = $request->input( 'order_by' );
            if( $orderBy && in_array( $orderBy, array_keys( self::$orderBys ) ) ) {
                self::$orderBys[ $orderBy ]( $builder, self::$order );
            } else {
                self::$orderBys[ $default ]( $builder, self::$order );
            }
        });
    }

    /**
     * @param $name
     * @param Closure $filter
     */
    public static function addFilter( $name, Closure $filter )
    {
        self::$filters[ $name ] = $filter;
    }

    /**
     * @param $class
     */
    public static function addFilters( $class )
    {
        try {
            $methods = ( new ReflectionClass( $class ) )->getMethods( ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED );
            foreach( $methods as $method ) {
                $method->setAccessible( true );
                self::addFilter( $method->name, $method->invoke( $class ) );
            }
        } catch( ReflectionException $e ) {}
    }

    /**
     * @param $name
     * @param Closure $filter
     */
    public static function addOrderBy( $name, Closure $filter )
    {
        self::$orderBys[ $name ] = $filter;
    }

    /**
     * @param $default
     * @return void
     */
    public static function orderByDefault( $default )
    {
        if( isset( self::$orderBys[ $default ] ) ) {
            self::$defaultOrderBy = $default;
        }
    }

    /**
     * @return mixed
     */
    abstract function preFilter();

}
