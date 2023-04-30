<?php
/**
 * Every request to the akit API returns an object with this structure
 */
class SessionResponse {
    public $success;
    public $msg;
    public $sessions;
}

/**
 * Request body for a request to create a session for an exercise
 */
class CreateSessionRequestBody {
    public $apiVersion = 2;

    /**
     * Should be an array of ExerciseDef objects
     */
    public $exercises;
    public $attributes = array(
        "integration-mode" => true
    );

    public function __construct(array $exercises) {
        $this->exercises = $exercises;
    }
}

/**
 * Tuple containing exercise ID and version
 */
class ExerciseDef {
    public $exerciseId;
    public $version;
    public $solutionMode;

    public function __construct($exerciseId, $version, $solutionMode) {
        $this->exerciseId = $exerciseId;
        $this->solutionMode = $solutionMode;
        $this->version = isset($version) && !empty($version) ? $version : 'latest';
    }
}

/**
 * Post to the akit API. 
 * @param string $url
 * @param CreateSessionRequestBody $data
 * @param string $apiKey
 * @return array An array of SessionResponse objects
 */
function akitPost($url, $data, $host, $apiKey): array {
    $url  = $host.$url;
    $dataString = json_encode($data);

    $args = array(
        'body' => $dataString,
        'headers' => array(
            "Content-type" => "application/json", 
            "x-api-key" => $apiKey
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

/** store a new reference to an exercise in the exercise map, which is stored
 *  in the session. The exercises in the map will be created by init_sessions().
 */
function addExerciseRef($exId, $exVersion, $isSolution, $showSolutionButton) {
    $placeHolder = uniqid('', true);

    if (isset($_SESSION['akit_exercise-map'])) {
        $map = $_SESSION['akit_exercise-map'];
    }
    else {
        $map = [];
    }
    
    $map[$placeHolder] = new ExerciseDef($exId, $exVersion, $isSolution);

    $_SESSION['akit_exercise-map'] = $map;
    $attrs = '';
    if($isSolution) $attrs = $attrs.'solution-mode ';
    if($showSolutionButton) $attrs = $attrs.'show-solution-button ';

    return "<div class='akit-wrapper'><akit-exercise cached-ref=\"$placeHolder\" $attrs></akit-exercise></div>";
}
/** store a new reference to an interaction in an exercise in the exercise map, if the exercise 
 * does not already exist. The exercises in the map will be created by init_sessions().
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

    return $isSolution
        ?"<div class='akit-wrapper'><akit-interaction cached-ref=\"$placeHolder\" ref-id=\"$refId\" solution-mode></akit-interaction></div>"
        :"<div class='akit-wrapper'><akit-interaction cached-ref=\"$placeHolder\" ref-id=\"$refId\"></akit-interaction></div>";
}


/** call /sessions/create of the AlgebraKiT API to create a session for all exercises
 *  in the exercise map. */
function init_sessions() {
    // obtain the map with all exercise references in this page, if any
    if (!isset($_SESSION['akit_exercise-map'])) return; 
    $map = $_SESSION['akit_exercise-map'];

    $apiKey = get_option('akit_api_key'); 
    if ($apiKey == null) {
        ?>
        <script>alert('AlgebraKIT API-Key was not set')</script>
        <?php
        return;
    }

    $host = "https://api.algebrakit.com";
    $widgetHost = "https://widgets.algebrakit.com/akit-widgets.min.js";
    // $host = "http://localhost:3000";
    // $widgetHost = "http://localhost:4000/akit-widgets.js";
    $theme = get_option("akit_theme");
    if($theme==null) $theme="akit";

    $exList = [];
    foreach (array_values($map) as $ex) {
        array_push($exList, $ex);
    }

    $data = new CreateSessionRequestBody($exList);
    $response = akitPost('/session/create', $data, $host, $apiKey);

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

    $widgetLoaderJs = file_get_contents(plugin_dir_path( __FILE__ ) .'akitConfig.js');
    $addCss = file_get_contents(plugin_dir_path( __FILE__ ) .'akit.css');
    $initJs = file_get_contents(plugin_dir_path( __FILE__ ) . 'akitInit.js');
    ?>

    <style><?php echo $addCss ?></style>
    <script>
        <?php echo $widgetLoaderJs ?>
        AlgebraKIT.cachedRefMap = <?php echo json_encode($resultMap) ?>;
    </script>
    <script src="<?php echo $widgetHost ?>"></script>
    <script><?php echo $initJs?></script>

    <?php

    $_SESSION['akit_exercise-map'] = null;
}

// make sure init_sessions() is called once when the page is created
add_action('wp_footer', 'init_sessions');