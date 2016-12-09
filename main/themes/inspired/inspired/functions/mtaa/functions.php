<?php
/**
 * General MN and Mtaa functions.
 *
 * @package Mtaa
 */

/**
 * Function for sending AJAX responses, present since MN 3.5.0, loaded only
 * for older versions for backward compatibility.
 */
if ( ! function_exists( 'mn_send_json' ) ) {
    /**
     * Send a JSON response back to an Ajax request.
     *
     * @since MN 3.5.0
     *
     * @param mixed $response Variable (usually an array or object) to encode as JSON, then print and die.
     */
    function mn_send_json( $response ) {
        @header( 'Content-Type: application/json; charset=' . get_option( 'blog_charset' ) );
        echo json_encode( $response );
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX )
            mn_die();
        else
            die;
    }
}

/**
 * Function for sending AJAX responses, present since MN 3.5.0, loaded only
 * for older versions for backward compatibility.
 */
if ( ! function_exists( 'mn_send_json_success' ) ) {
    /**
     * Send a JSON response back to an Ajax request, indicating success.
     *
     * @since MN 3.5.0
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     */
    function mn_send_json_success( $data = null ) {
        $response = array( 'success' => true );

        if ( isset( $data ) )
            $response['data'] = $data;

        mn_send_json( $response );
    }
}

/**
 * Function for sending AJAX responses, present since MN 3.5.0, loaded only
 * for older versions for backward compatibility.
 */
if ( ! function_exists( 'mn_send_json_error' ) ) {
    /**
     * Send a JSON response back to an Ajax request, indicating failure.
     *
     * @since MN 3.5.0
     *
     * @param mixed $data Data to encode as JSON, then print and die.
     */
    function mn_send_json_error( $data = null ) {
        $response = array( 'success' => false );

        if ( isset( $data ) )
            $response['data'] = $data;

        mn_send_json( $response );
    }
}
