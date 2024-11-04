<?php

namespace LaraWp\includes\Http;

trait Validator {
    // Validate if field is required
    public function validateRequired( $field, $value ) {
        if ( empty( $value ) ) {
            return "The {$field} field is required.";
        }
        return null;
    }

    // Validate email format
    public function validateEmail( $field, $value ) {
        if ( !filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
            return "The {$field} must be a valid email address.";
        }
        return null;
    }

    // Validate maximum length
    public function validateMax( $field, $value, $param ) {
        if ( strlen( $value ) > (int)$param ) {
            return "The {$field} must not be longer than {$param} characters.";
        }
        return null;
    }

    // Validate numeric value
    public function validateNumeric( $field, $value ) {
        if ( !is_numeric( $value ) ) {
            return "The {$field} must be a numeric value.";
        }
        return null;
    }

}