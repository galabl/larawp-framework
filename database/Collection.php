<?php

namespace LaraWp\Database;

class Collection implements \ArrayAccess, \IteratorAggregate, \Countable {
    public $items;
    public $pagination = [];

    public function __construct( array $items = [], $pagination = [] ) {
        $this->items = $items;
        if ( empty( $pagination ) ) {
            unset($this->pagination);
        }
    }

    public function asArray() {
        return $this->items;
    }

    public function first() {
        return $this->items[ 0 ] ?? null;
    }

    public function count(): int {
        return count( $this->items );
    }

    public function getIterator(): \ArrayIterator {
        return new \ArrayIterator( $this->items );
    }

    public function offsetExists( $offset ): bool {
        return isset( $this->items[ $offset ] );
    }

    public function offsetGet( $offset ): mixed {
        return $this->items[ $offset ];
    }

    public function offsetSet( $offset, $value ): void {
        if ( is_null( $offset ) ) {
            $this->items[] = $value;
        }
        else {
            $this->items[ $offset ] = $value;
        }
    }

    public function offsetUnset( $offset ): void {
        unset( $this->items[ $offset ] );
    }

    public function ajax() {
        return array_map( function ($data) {
            return $data->ajax();
        }, $this->items );
    }
}

