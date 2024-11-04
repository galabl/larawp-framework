<?php

namespace LaraWp\Includes\Database;

class Blueprint {
    protected $columns = [];
    protected $indexes = [];
    protected $currentColumnType;
    protected $debug;

    public function __construct( $debug = false ) {
        $this->debug = $debug;
    }

    public function __call( $method, $parameters ) {
        // Dynamically handle all column types as methods
        if ( method_exists( $this, $method ) ) {
            return $this->$method( ...$parameters );
        }

        // Otherwise, assume it’s a column definition method
        return $this->addColumn( $method, $parameters[ 0 ] );
    }

    /**
     * @param $type
     * @param $name
     *
     * @return $this
     */
    public function addColumn( $type, $name ): static {
        $this->currentColumnType = $type; // Store the current column type for chaining
        $this->columns[] = compact( 'type', 'name' );
        return $this;
    }

    public function increments( $name ): static {
        $this->addColumn( 'BIGINT AUTO_INCREMENT', $name );
        return $this->primary( $name ); // Automatically set as primary key
    }

    public function unsigned(): static {
        // Append UNSIGNED to the current column type
        if ( $this->currentColumnType ) {
            $this->currentColumnType .= ' UNSIGNED';
        }
        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function primary( $name = null ): static {
        // If no name is provided, use the name of the last column defined
        if ( $name === null && isset( $this->columns ) ) {
            $lastColumnKey = array_key_last( $this->columns );
            $name = $this->columns[ $lastColumnKey ][ 'name' ];
        }

        $this->indexes[] = [ 'type' => 'primary', 'column' => $name ];
        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function index( $name ): static {
        $this->indexes[] = [ 'type' => 'index', 'column' => $name ];
        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function unique( $name = null ): static {
        if ( $name === null && isset( $this->columns ) ) {
            $lastColumnKey = array_key_last( $this->columns );
            $name = $this->columns[ $lastColumnKey ][ 'name' ];
        }
        $this->indexes[] = [ 'type' => 'unique', 'column' => $name ];
        return $this;
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function integer( $name ): static {
        return $this->addColumn( 'INT', $name );
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function bigInteger( $name ): static {
        return $this->addColumn( 'BIGINT', $name );
    }

    /**
     * @param $name
     * @param $length
     *
     * @return $this
     */
    public function string( $name, $length = 255 ): static {
        return $this->addColumn( "VARCHAR($length)", $name );
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function boolean( $name ): static {
        return $this->addColumn( 'TINYINT', $name ); // Use TINYINT for boolean
    }

    /**
     * @param $name
     *
     * @return $this
     */
    public function text( $name ): static {
        return $this->addColumn( 'TEXT', $name );
    }

    /**
     * @param string $name
     * @param array  $values
     *
     * @return $this
     */
    public function enum( string $name, array $values ): static {
        $enumValues = implode( "','", array_map( 'esc_sql', $values ) );
        return $this->addColumn( "ENUM('$enumValues')", $name );
    }

    /**
     * @param string $name
     * @param int    $precision
     * @param int    $scale
     *
     * @return $this
     */
    public function decimal( $name, $precision = 8, $scale = 2 ): static {
        return $this->addColumn( "DECIMAL($precision, $scale)", $name );
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function date( $name ): static {
        return $this->addColumn( 'DATE', $name );
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function datetime( $name ): static {
        return $this->addColumn( 'DATETIME', $name );
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function timestamp( $name ): static {
        return $this->addColumn( 'TIMESTAMP', $name );
    }

    public function default( $value ) {
        // Set the default value for the last column defined
        if ( !empty( $this->columns ) ) {
            $lastColumnKey = array_key_last( $this->columns );
            $this->columns[ $lastColumnKey ][ 'default' ] = $value;

            // For debug: output the default value set
            if ( $this->debug ) {
                error_log( "Set Default for Column: $lastColumnKey to '" . ( is_null( $value ) ? 'NULL' : $value ) . "'\n" );
            }
        }

        return $this; // Return $this for method chaining
    }

    /**
     * Add created_at and updated_at timestamp columns.
     *
     * @return $this
     */
    public function timestamps(): static {
        $this->addColumn( 'DATETIME', 'created_at' )
             ->default( 'CURRENT_TIMESTAMP' );
        $this->addColumn( 'DATETIME', 'updated_at' )
             ->default( 'CURRENT_TIMESTAMP' )
             ->onUpdate( 'CURRENT_TIMESTAMP' );

        return $this;
    }

    /**
     * Add `onUpdate` functionality to a column.
     *
     * @param string $value
     *
     * @return $this
     */
    public function onUpdate( $value ): static {
        // Set the "on update" timestamp for the last column defined
        if ( !empty( $this->columns ) ) {
            $lastColumnKey = array_key_last( $this->columns );
            $this->columns[ $lastColumnKey ][ 'onUpdate' ] = $value;
        }

        return $this; // Return $this for method chaining
    }

    public function nullable(): static {
        // Set the nullable flag for the last column defined
        if (!empty($this->columns)) {
            $lastColumnKey = array_key_last($this->columns);
            $this->columns[$lastColumnKey]['nullable'] = true;
        }
        return $this; // Return $this for method chaining
    }


    /**
     * @param $tableName
     *
     * @return string
     */
    /**
     * @param $tableName
     *
     * @return string
     */
    public function getTableSQL( $tableName ): string {
        global $wpdb;

        $sql = "CREATE TABLE $tableName (";
        $columns = [];
        foreach ( $this->columns as $column ) {
            $columnDefinition = "`{$column['name']}` {$column['type']}";

            // Check if the column is nullable
            if (isset($column['nullable']) && $column['nullable']) {
                $columnDefinition .= ' NULL';
            } else {
                $columnDefinition .= ' NOT NULL'; // Default to NOT NULL if not specified
            }

            // Check if we have an `onUpdate` defined for this column
            if ( isset( $column['onUpdate'] ) ) {
                $columnDefinition .= " ON UPDATE {$column['onUpdate']}";
            }

            $columns[] = $columnDefinition;
        }
        $sql .= implode( ", ", $columns );

        // Add primary key
        foreach ( $this->indexes as $index ) {
            if ( $index[ 'type' ] === 'primary' ) {
                $sql .= ", PRIMARY KEY ({$index['column']})";
            }
            elseif ( $index[ 'type' ] === 'index' ) {
                $sql .= ", INDEX ({$index['column']})";
            }
            elseif ( $index[ 'type' ] === 'unique' ) {
                $sql .= ", UNIQUE ({$index['column']})";
            }
        }

        $sql .= ")";

        // Debug output only once
        if ( $this->debug ) {
            error_log( "Generated SQL:\n");
            error_log( $sql . "\n");
        }

        return $sql;
    }
}
