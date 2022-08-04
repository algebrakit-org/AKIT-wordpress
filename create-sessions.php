<?php

/**
 * Post to the akit API. 
 * @param string $url
 * @param CreateSessionRequestBody $data
 * @param string $apiKey
 * @return array An array of SessionResponse objects
 */
function akitPost($url, $data): array {
    global $HOST, $API_KEY;

    $url  = $HOST.$url;
    $dataString = json_encode($data);
    if ($API_KEY == null) {
        ?>
        <script>alert('AlgebraKIT API-Key was not set')</script>
        <?php
        return null;
    }
    
    $args = array(
        'body' => $dataString,
        'headers' => array(
            "Content-type" => "application/json", 
            "x-api-key" => $API_KEY
        )
    );
    $response = wp_remote_post($url, $args);
    $body = json_decode(wp_remote_retrieve_body($response));
    if (!is_array($body) && isset($body->success) && $body->success == false) {
        $sess = new SessionResponse();
        $sess->success = false;
        $sess->msg = 'Unkown error';
        if (isset($body->error)) {
            $sess->msg = $body->error;
        }
        return [
            $sess
        ];
    }
    return $body;
}


/** call /sessions/create of the AlgebraKiT API to efficiently create a session for all exercises
 *  in the exercise map. */
function init_sessions() {
    global $WIDGET_HOST, $THEME;

    // obtain the map with all exercise references in this page, if any
    if (!isset($_SESSION['akit_exercise-map'])) return; 
    $map = $_SESSION['akit_exercise-map'];

    $exList = [];
    foreach (array_values($map) as $ex) {
        array_push($exList, $ex);
    }

    $attributes = array(
        "integration-mode" => true
    );

    $data = new CreateSessionRequestBody($exList, $attributes);
    $response = akitPost('/session/create', $data);
    if($response==null) return;

    $resultMap = [];

    //create map from cached-ref to session data
    for ($i = 0; $i < count($response); $i++) {  // for each requested exercise-id...
        $responseEntry = $response[$i];
        $exDef = $exList[$i];
        if ($responseEntry->success == false || !isset($responseEntry->sessions)) {
            continue;
        }
        foreach($responseEntry->sessions as $session) { // for each session created for this exercise...
            if ($session->success == false) {
                continue;
            }
            //Find the chachedRef for this exercise
            $cachedRef = array_search($exDef, $map);
            $resultMap[$cachedRef] = $session;
        }
    }

    $widgetLoaderJs = file_get_contents(plugin_dir_path( __FILE__ ) . 'widgetLoader.js');
    $widgetLoaderJs = str_replace('${WIDGET_HOST}', $WIDGET_HOST, $widgetLoaderJs);
    $widgetLoaderJs = str_replace('${THEME}', $THEME, $widgetLoaderJs);

    ?>
    <script>
        <?php echo $widgetLoaderJs ?>
        AlgebraKIT.cachedRefMap = <?php echo json_encode($resultMap) ?>;
    </script>
    <?php

    $_SESSION['akit_exercise-map'] = null;
}

/** 
 * call /sessions/create of the AlgebraKiT API to create a single session. Returns the response of /session/create 
 * @param $exRef reference to an exercise as described in /session/create
 *        e.g. {exerciseId: ..., version: ...}, or {exerciseSpec: ....}
 * */
function create_single_session($exRef) {
    $exList = [$exRef];

    $data = new CreateSessionRequestBody($exList);
    $response = akitPost('/session/create', $data);
    return $response[0];
}


/** store a new reference to an exercise in the exercise map, which is stored
 *  in the session. The exercises in the map will be created by init_sessions().
 * 
 * @return a html element <akit-wp-exercise> that will be resolved by javascript code to 
 *         a working exercise. See widgetLoader.js
 */
function addExerciseRef($exId, $exVersion, $isSolution, $repeatButton, $editButton) {
    $placeHolder = uniqid('', true);

    if (isset($_SESSION['akit_exercise-map'])) {
        $map = $_SESSION['akit_exercise-map'];
    }
    else {
        $map = [];
    }
    
    $map[$placeHolder] = new ExerciseDef($exId, $exVersion, $isSolution);

    $_SESSION['akit_exercise-map'] = $map;

    $tag="<akit-wp-exercise cached-ref=\"$placeHolder\" exercise-id=\"$exId\"";
    
    if($isSolution) $tag = $tag.' solution-mode';
    if($repeatButton) $tag = $tag.' repeat-button';
    if($editButton) $tag = $tag.' edit-button';
    $tag = $tag.'></akit-wp-exercise>';

    return $tag;
}

/** store a new reference to an interaction in an exercise in the exercise map, if the exercise 
 * does not already exist. The exercises in the map will be created by init_sessions().
 * 
 * @return a html element <akit-interaction> that will resolve to a working interaction
 */
function addInteractionRef($exId, $refId, $exVersion, $isSolution) {

    if (isset($_SESSION['akit_exercise-map'])) {
        $map = $_SESSION['akit_exercise-map'];
    }
    else {
        $map = [];
    }
    
    // check if the exercise is already present
    $exDef = null;
    $placeHolder = null;

    foreach ($map as $key => $value) {
        if(strcmp($value->exerciseId, $exId)==0) {
            $exDef = $value;
            $placeHolder = $key;
            break;
        }
    }    
    if($exDef==null) {
        $placeHolder = uniqid('', true);
        $exDef = new ExerciseDef($exId, $exVersion, $isSolution);
        $map[$placeHolder] = $exDef;
    }

    $_SESSION['akit_exercise-map'] = $map;

    $tag="<akit-interaction cached-ref=\"$placeHolder\" exercise-id=\"$exId\" ref-id=\"$refId\"";
    if($isSolution) $tag = $tag.' solution-mode';
    $tag = $tag.'></akit-interaction>';

    return $tag;
}



// make sure init_sessions() is called once when the page is created
add_action('wp_footer', 'init_sessions');