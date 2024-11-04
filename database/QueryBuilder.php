<?php

namespace LaraWp\Database;

use LaraWp\Includes\Support\LaraWpException;
use wpdb;

class QueryBuilder {
    protected $table;
    protected $query;
    protected $select = [ '*' ];
    protected $bindings = [];
    protected $modelClass;
    protected $wpdb;
    protected $withRelations;

    public function __construct( $table, $modelClass = null ) {
        global $wpdb;
        $this->wpdb = $wpdb; // Set the global $wpdb instance
        $this->table = $wpdb->prefix . $table;
        $this->modelClass = $modelClass;
        $this->query = ''; // Initialize query as empty
    }

    public function select( $columns ) {
        if ( is_string( $columns ) ) {
            $columns = func_get_args();
        }

        $this->select = $columns;

        return $this;
    }

    // WHERE clause
    public function where( $column, $operator = '=', $value = null ) {
        if ( empty( $this->bindings ) ) {
            $this->query .= " WHERE {$column} {$operator} %s";
        }
        else {
            $this->query .= " AND {$column} {$operator} %s";
        }
        $this->bindings[] = $value;
        return $this;
    }


    // WHERE LIKE clause
    public function whereLike( $column, $value ) {
        $this->query .= " WHERE {$column} LIKE %s";
        $this->bindings[] = '%' . $value . '%';
        return $this;
    }

    // WHERE NOT LIKE clause
    public function whereNotLike( $column, $value ) {
        $this->query .= " WHERE {$column} NOT LIKE %s";
        $this->bindings[] = '%' . $value . '%';
        return $this;
    }

    // WHERE DATE clause
    public function whereDate( $column, $operator, $value ) {
        $this->query .= " WHERE DATE({$column}) {$operator} %s";
        $this->bindings[] = $value;
        return $this;
    }

    // WHERE DATE LIKE clause
    public function whereDateLike( $column, $value ) {
        $this->query .= " WHERE DATE({$column}) LIKE %s";
        $this->bindings[] = '%' . $value . '%';
        return $this;
    }

    // OR WHERE clause
    public function orWhere( $column, $operator = '=', $value = null ) {
        if ( empty( $this->bindings ) ) {
            $this->query .= " WHERE {$column} {$operator} %s";
        } else {
            $this->query .= " OR {$column} {$operator} %s";
        }
        $this->bindings[] = $value;
        return $this;
    }

    // OR WHERE LIKE clause
    public function orWhereLike( $column, $value ) {
        if ( empty( $this->bindings ) ) {
            $this->query .= " WHERE {$column} LIKE %s";
        } else {
            $this->query .= " OR {$column} LIKE %s";
        }
        $this->bindings[] = '%' . $value . '%';
        return $this;
    }

	// WHERE NULL clause
	public function whereNull($column) {
		if (empty($this->bindings)) {
			$this->query .= " WHERE {$column} IS NULL";
		} else {
			$this->query .= " AND {$column} IS NULL";
		}
		return $this;
	}

	// Add JOIN clause
    public function join( $table, $first, $operator, $second ) {
        $this->query .= " JOIN {$table} ON {$first} {$operator} {$second}";
        return $this;
    }

    // Add a LIMIT clause
    public function limit( $limit ) {
        $this->query .= " LIMIT %d";
        $this->bindings[] = $limit;
        return $this;
    }

    // Add OFFSET clause
    public function offset( $offset ) {
        $this->query .= " OFFSET %d";
        $this->bindings[] = $offset;
        return $this;
    }

    // Paginate method
    public function paginate( $perPage = 15, $currentPage = 1 ) {
        $offset = ( $currentPage - 1 ) * $perPage;

        // Count total records before limiting
        $totalRecords = $this->count();

        // Set limit and offset
        $this->limit( $perPage )->offset( $offset );

        // Fetch paginated results
        $results = $this->get();

        // Calculate pagination details
        $totalPages = ceil( $totalRecords / $perPage );
        $pagination = [
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => (int) $currentPage,
            'last_page' => $totalPages,
            'from' => $offset + 1,
            'to' => $offset + count( $results ),
        ];

        // Attach pagination details to results
        $results->pagination = $pagination;

        return $results;
    }

    public function count( $column = '*' ) {
        // Start building the query with a SELECT COUNT statement
        $query = "SELECT COUNT({$column}) AS aggregate FROM {$this->table}";
        $query = ( $this->bindings ) ? $this->wpdb->prepare( $query, ...$this->bindings ) : $query;

        // Prepare and execute the query with bindings
        $result = $this->wpdb->get_var( $query );

        return (int)$result; // Return the count as an integer
    }

    public function sum( $column ) {
        // Start building the query with a SELECT SUM statement
        $this->query = "SELECT SUM({$column}) AS aggregate FROM {$this->table}";

        // Prepare and execute the query with bindings
        $result = $this->wpdb->get_var( $this->wpdb->prepare( $this->query, ...$this->bindings ) );

        return (float)$result; // Return the sum as a float
    }


    // Fetch all results
    public function get() {
        $query = "SELECT " . implode( ',', $this->select ) . " FROM {$this->table}";
        $this->query = $query . $this->query;
        $this->query = ( $this->bindings ) ? $this->wpdb->prepare( $this->query, ...$this->bindings ) : $this->query;
        error_log("QUERY get(): $this->query");
        $results = $this->wpdb->get_results( $this->query );

        $models = [];
        foreach ( $results as $result ) {
            $model = new $this->modelClass; // Dynamically instantiate the model
            foreach ( $result as $key => $value ) {
                $model->{$key} = $value; // Set the properties on the model
            }
            if ( !empty( $this->withRelations ) ) {
                foreach ( $this->withRelations as $relation ) {
                    $model->{$relation} = $model->$relation()->asArray();
                }
            }
            if ( wp_doing_ajax() ) {
                $models[] = $model->ajax();
            } else {
                $models[] = $model;
            }
        }

        return new Collection( $models ); // Return the results as a Collection
    }

    /**
     * @throws \Exception
     */
    public function delete() {
        // Ensure a WHERE clause exists to avoid deleting all records unintentionally
        if ( empty( $this->bindings ) ) {
            throw new LaraWpException( 'No WHERE condition set for delete.' );
        }

        // Build the DELETE query using the table name and the WHERE clause
        $query = "DELETE FROM {$this->table} {$this->query}";

        // Prepare the query with the WHERE clause bindings
        $preparedQuery = $this->wpdb->prepare( $query, ...$this->bindings );
        error_log('DELETE QUERY: ' . $preparedQuery);

        // Execute the query
        $result = $this->wpdb->query( $preparedQuery );

        // Check for errors and log if necessary
        if ( $this->wpdb->last_error ) {
            error_log( 'SQL Error: ' . $this->wpdb->last_error );
            throw new LaraWpException( "SQL Error: " . $this->wpdb->last_error );
        }

        return $result; // Returns the number of affected rows or false if there was an error
    }

    // Fetch the first result
    public function first() {
        return $this->limit( 1 )->get()->first();
    }

    // CREATE method for inserting new records
    public function create( $data ) {
        $columns = implode( ', ', array_keys( $data ) );
        $placeholders = implode( ', ', array_fill( 0, count( $data ), '%s' ) );
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";

        $this->wpdb->query( $this->wpdb->prepare( $sql, ...array_values( $data ) ) );

        return $this->wpdb->insert_id; // Return the ID of the newly inserted record
    }

    // UPDATE method for modifying records
    public function update( $data ) {
        // Prepare the SET clause
        $setClause = '';
        $bindings = [];
        foreach ( $data as $column => $value ) {
            $setClause .= "`{$column}` = %s, ";
            $bindings[] = $value;
        }
        $setClause = rtrim( $setClause, ', ' );

        // If no WHERE condition is set, throw an error
        if ( empty( $this->bindings ) ) {
            throw new LaraWpException( 'No WHERE condition set for update.' );
        }

        // Build the full query with the SET clause and existing WHERE clause from $this->query
        $query = "UPDATE {$this->table} SET {$setClause} {$this->query}";

        // Prepare the final query with both the SET and WHERE clause bindings
        $finalBindings = array_merge( $bindings, $this->bindings );

        // Execute the query
        $result = $this->wpdb->query( $this->wpdb->prepare( $query, ...$finalBindings ) );

        // Check for errors and log
        if ( $this->wpdb->last_error ) {
            error_log( 'SQL Error: ' . $this->wpdb->last_error );
            throw new LaraWpException('SQL Error: ' . $this->wpdb->last_error );
        }

        return $result; // Returns the number of affected rows or false if there was an error
    }

    public function with( $relation ) {
        // If only a single relation is passed, convert it to an array
        if ( is_string( $relation ) ) {
            $relation = func_get_args();
        }

        $this->withRelations = $relation;

        return $this;
    }

    public function pluck($column, $key = null) {
        // Start building the SELECT query with the requested columns
        $query = "SELECT " . (is_null($key) ? $column : "{$key}, {$column}") . " FROM {$this->table}";

        // Append any existing WHERE conditions from the query
        $query .= $this->query;

        // Prepare the query with the necessary bindings
        $preparedQuery = ( $this->bindings ) ? $this->wpdb->prepare( $query, ...$this->bindings ) : $query;

        // Execute the query and fetch results as an associative array
        $results = $this->wpdb->get_results($preparedQuery, ARRAY_A);

        // Process the results and build the final pluck result
        $pluckResult = [];
        foreach ($results as $result) {
            if (!is_null($key) && isset($result[$key])) {
                // If key is provided, use it as the index
                $pluckResult[$result[$key]] = $result[$column];
            } else {
                // Otherwise, push the column values into the result array
                $pluckResult[] = $result[$column];
            }
        }

        return $pluckResult;
    }

}
