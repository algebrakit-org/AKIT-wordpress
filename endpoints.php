<?php

add_action( 'rest_api_init', function () {
  register_rest_route( 'akit/v1', '/session/create', array(
    'methods' => 'POST',
    'callback' => '_akit_create_session_endpoint',
  ) );
} );

function _akit_create_session_endpoint( WP_REST_Request $request ) {
    $parameters = $request->get_json_params();
    // return print_r($parameters, true);

    if(!is_array($parameters['exercises'])) {
        return new WP_Error( 'invalid', 'Invalid input: argument "exercises" is missing', array( 'status' => 400 ) );
    }
    if(count($parameters['exercises'])!=1) {
      $len = count($parameters['exercises']);
      return new WP_Error( 'invalid', "Invalid input: 'exercises' should contain a single exercise (current length is $len)", array( 'status' => 400 ) );
    }

    $ex = $parameters['exercises'][0];
    $resp = create_single_session($ex);

    if($resp->success) {
      return $resp->sessions[0];
    } else {
      return new WP_Error( 'invalid', "Could not generate session", array( 'status' => 400 ) );
    }
  }

?>
