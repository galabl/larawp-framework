<?php

namespace LaraWp\Includes\Http;

class Response {
    public function json( $data = null, $code = 200 ) {
        wp_send_json( $data, $code );
    }

    public function send( $data = null, $code = 200 ) {
        wp_send_json( $data, $code );
    }

    public function send_success( $data = null, $code = null ) {
        wp_send_json_success( $data, $code );
    }

    public function send_error( $data = null, $code = null ) {
        wp_send_json_error( $data, $code );
    }
}