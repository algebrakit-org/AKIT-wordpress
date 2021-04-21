<?php

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

    public function __construct($exerciseId, $version) {
        $this->exerciseId = $exerciseId;
        $this->version = isset($version) && !empty($version) ? $version : 'latest';
    }
}

function akit_settings_shortcode($atts = array()) {
    $apiKey = get_atts_value($atts, 'api-key');
    $env = get_atts_value($atts, 'env');
    $theme = get_atts_value($atts, 'theme');
    if ($apiKey != null) {
        $_SESSION['akit_api-key'] = $apiKey;
    }
    if ($env != null) {
        $_SESSION['akit_env'] = $env;
    }
    if ($theme != null) {
        $_SESSION['akit_theme'] = $theme;
    }
}

function akit_exercise_shortcode($atts = array()) {
    if (!array_key_exists('exercise-id', $atts)) {
        return;
    }
    if (array_key_exists('version', $atts)) {
        $exVersion = $atts['version'];
    }
    else {
        $exVersion = 'latest';
    }
    $exId = $atts['exercise-id'];
    $placeHolder = uniqid('', true);

    if (isset($_SESSION['akit_exercise-map'])) {
        $map = $_SESSION['akit_exercise-map'];
    }
    else {
        $map = [];
    }
    
    $map[$placeHolder] = new ExerciseDef($exId, $exVersion);

    $_SESSION['akit_exercise-map'] = $map;

    return "<akit-exercise cached-ref=\"$placeHolder\"></akit-exercise>";
}

function get_session_var($key, $default = null) {
    if (isset($_SESSION[$key])) {
        return $_SESSION[$key];
    }
    return $default;
}

function get_atts_value(array $atts, $key) {
    if (array_key_exists($key, $atts)) {
        return $atts[$key];
    }
    return null;
}

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
    return json_decode(wp_remote_retrieve_body($response));
}

function init_sessions() {
    $exMap = get_session_var('akit_exercise-map');
    if ($exMap == null) {
        return;
    }
    $map = $_SESSION['akit_exercise-map'];

    $apiKey = get_session_var('akit_api-key');
    if ($apiKey == null) {
        ?>
        <script>alert('AlgebraKIT API-Key was not set')</script>
        <?php
        return;
    }

    $exList = [];
    foreach (array_values($map) as $ex) {
        array_push($exList, $ex);
    }

    $env = get_session_var('akit_env', 'prod');
    $theme = get_session_var('akit_theme', 'akit');

    switch($env) {
        case 'staging':
            $host = "https://staging.algebrakit.eu";
            $widgetHost = "https://widgets.staging.algebrakit.eu";
            break;
        case 'local':
            $host = "http://localhost:3000";
            $widgetHost = "http://localhost:4000";
            break;
        default:
            $host = "https://algebrakit.eu";
            $widgetHost = "https://widgets.algebrakit.eu";
            break;
    }

    $data = new CreateSessionRequestBody($exList);
    $response = akitPost('/session/create', $data, $host, $apiKey);

    $resultMap = [];

    //create map from cached-ref to session data
    for ($i = 0; $i < count($response); $i++) {
        $responseEntry = $response[$i];
        $exDef = $exList[$i];
        if ($responseEntry->success == false || !isset($responseEntry->sessions)) {
            continue;
        }
        foreach($responseEntry->sessions as $session) {
            if ($session->success == false) {
                continue;
            }
            //Find the chachedRef for this exercise
            $cachedRef = array_search($exDef, $exMap);
            $resultMap[$cachedRef] = $session->html;
        }
    }

    $widgetLoaderJs = file_get_contents(plugin_dir_path( __FILE__ ) . 'widgetLoader.js');
    $widgetLoaderJs = str_replace('${WIDGET_HOST}', $widgetHost, $widgetLoaderJs);
    $widgetLoaderJs = str_replace('${THEME}', $theme, $widgetLoaderJs);

    ?>
    <script>
        <?php echo $widgetLoaderJs ?>
        AlgebraKIT.cachedRefMap = <?php echo json_encode($resultMap) ?>;
    </script>
    <?php

    $_SESSION['akit_exercise-map'] = null;
}

add_shortcode('akit-settings', 'akit_settings_shortcode'); 
add_shortcode('akit-exercise', 'akit_exercise_shortcode'); 
add_action('wp_footer', 'init_sessions');