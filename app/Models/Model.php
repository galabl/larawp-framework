<?php

namespace LaraWp\App\Models;

use LaraWp\Database\QueryBuilder;
use LaraWp\Database\Collection;

abstract class Model {
    protected static $table;
    protected static $primaryKey = 'id';
    public static $timestamps = false;

    public array $attributes = [];

    public function __construct( $attributes = [] ) {
        foreach ( $attributes as $key => $value ) {
            $this->{$key} = $value;
        }
    }

    public function __set($name, $value) {
        $this->attributes[$name] = $value;
    }

    public function __get($name) {
        return $this->attributes[$name] ?? null;
    }

    public function getPrimaryKey() {
        return $this->{static::$primaryKey};
    }

    public static function query() {
        return new QueryBuilder( static::$table, static::class );
    }

    public static function all() {
        return static::query()->get();
    }

    public static function find( $id ) {
        return static::query()->where( static::$primaryKey, '=', $id )->first();
    }

    public static function select( $columns ) {
        return static::query()->select( $columns );
    }

    public static function where( $column, $operator = '=', $value = null ) {
        return static::query()->where( $column, $operator, $value );
    }

    public static function orWhere( $column, $operator = '=', $value = null ) {
        return static::query()->orWhere( $column, $operator, $value );
    }

    public static function whereNot( $column, $operator = '=', $value = null ) {
        return static::query()->whereNot( $column, $operator, $value );
    }

    public static function whereLike( $column, $value ) {
        return static::query()->whereLike( $column, $value );
    }

    public static function whereNotLike( $column, $value ) {
        return static::query()->whereNotLike( $column, $value );
    }

    public static function whereDate( $column, $date ) {
        return static::query()->whereDate( $column, '=', $date );
    }

    public static function whereDateLike( $column, $date ) {
        return static::query()->whereDateLike( $column, $date );
    }

    public static function whereBetween( $column, $start, $end ) {
        return static::query()->whereBetween( $column, $start, $end );
    }

    public static function whereDateBetween( $column, $startDate, $endDate ) {
        return static::query()->whereDateBetween( $column, $startDate, $endDate );
    }

	public static function whereNull($column) {
        return static::query()->whereNull( $column );
    }

    public static function count() {
        return static::query()->count();
    }

    public static function sum( $column ) {
        return static::query()->sum( $column );
    }

    public static function create( array $attributes ) {
        return static::query()->create( $attributes );
    }

    public static function update( array $attributes) {
        return static::query()->update( $attributes );
    }

    public static function first() {
        return static::query()->first();
    }

    public static function paginate( $perPage = 15, $current_page = 1 ) {
        return static::query()->paginate( $perPage, $current_page );
    }

    // Find or create a model by attributes
    public static function findOrCreate( $attributes ) {
        $instance = static::query()->where( $attributes )->first();
        if ( $instance ) {
            return $instance;
        }

        return static::create( $attributes );
    }

    // Find or update a model by attributes
    public static function findOrUpdate( $attributes ) {
        $instance = static::query()->where( $attributes )->first();
        if ( $instance ) {
            return $instance->update( $attributes );
        }

        return static::create( $attributes );
    }

    public function belongsTo( $relatedModel, $foreignKey = null, $localKey = 'id' ) {
        // Set the foreign key to use
        $foreignKey = $foreignKey ?? static::$primaryKey;

        // If the foreign key is null, return null immediately
        if ( is_null( $this->{$foreignKey} ) ) {
            return null;
        }

        // Attempt to find the related model by the foreign key and local key
        $relatedModelInstance = $relatedModel::where( $localKey, '=', $this->{$foreignKey} )->first();

        // If no related model exists, return null
        if ( !$relatedModelInstance ) {
            return null;
        }

        return $relatedModelInstance;
    }

    public function hasOne( $relatedModel, $foreignKey = null, $localKey = 'id' ) {
        // Set the foreign key to use if not provided
        $foreignKey = $foreignKey ?? static::$primaryKey;

        // If the local key is null, return null immediately
        if ( is_null( $this->{$localKey} ) ) {
            return null;
        }

        // Attempt to find the related model where the foreign key matches the local key
        $relatedModelInstance = $relatedModel::where( $foreignKey, '=', $this->{$localKey} )->first();

        // If no related model exists, return null
        if ( !$relatedModelInstance ) {
            return null;
        }

        return $relatedModelInstance;
    }

    public function hasMany( $relatedModel, $foreignKey = null, $localKey = null ) {
        // Set the foreign key to use
        $foreignKey = $foreignKey ?? static::$primaryKey;

        // Attempt to find related models by the foreign key
        $relatedModels = $relatedModel::where( $foreignKey, '=', $this->{$localKey} )->get();

        // Return the related models, even if empty
        return $relatedModels;
    }


    public function hasManyThrough( $relatedModel, $throughModel, $foreignKey = null, $secondForeignKey = null ) {
        // Check if the foreign key is null
        if ( is_null( $this->{$foreignKey} ) ) {
            return new Collection( [] );
        }

        // Query the related models through the intermediate model
        $relatedModels = $relatedModel::join( $throughModel::getTable(), "{$throughModel::getTable()}.{$secondForeignKey}", '=', $relatedModel::getTable() . '.id' )
                                      ->where( "{$throughModel::getTable()}.{$foreignKey}", '=', $this->{$foreignKey} )
                                      ->get()
        ;

        return $relatedModels;
    }


    public function belongsToMany( $relatedModel, $pivotTable = null, $foreignKey = null, $relatedKey = null ) {
        // Check if the foreign key is null, return an empty Collection if true
        if ( is_null( $this->{$foreignKey} ) ) {
            return new Collection( [] );
        }

        // Retrieve related models based on pivot table
        $relatedModels = $relatedModel::join( $pivotTable, "{$pivotTable}.{$foreignKey}", '=', $this->{$foreignKey} )
                                      ->get()
        ;

        return $relatedModels;
    }


    public function belongsToManyThrough( $relatedModel, $intermediateModel, $pivotTable, $foreignKey = null, $relatedKey = null ) {
        $foreignKey = $foreignKey ?? ( static::$primaryKey );
        $relatedKey = $relatedKey ?? $relatedModel::$primaryKey;

        $relatedModels = $relatedModel::query()
                                      ->join( $pivotTable, $pivotTable . '.' . $relatedKey, '=', $relatedModel::getTable() . '.' . $relatedKey )
                                      ->where( $pivotTable . '.' . $foreignKey, '=', $this->{$foreignKey} )
                                      ->get()
        ;

        if ( $relatedModels->count() > 0 ) {
            return new Collection( $relatedModels );
        }

        return null;
    }

    public static function getTable() {
        return self::$table;
    }

    public static function with( $relation ) {
        return static::query()->with($relation);
    }

    public function ajax() {
        return (object) $this->attributes;
    }
}
