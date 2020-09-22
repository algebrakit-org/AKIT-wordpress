<?php
/**
 * Plugin Name: AlgebraKiT
 * Plugin URI: https://docs.algebrakit-learning.com/plugins/wordpress/
 * Description: Running AlgebraKiT Exercises on your wordpress website
 * Requires PHP: 7.2
 */

//Akit Specific vars and functions
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

/**
 * Post to the akit API. 
 * @param string $url
 * @param CreateSessionRequestBody $data
 * @param string $apiKey
 * @return array An array of SessionResponse objects
 */
function akitPost($url, $data, $apiKey): array {
    $url  = "https://algebrakit.eu".$url;
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

/**
 * Create a new session for the exercise with the specified ID and the specified version number
 * @param string $exerciseId
 * @param string $majorVersion Can be a number, "latest", "" or null. When empty or null, 
 * "latest" is assumed. If this value is (assumed to be) latest, the version most recently saved in the AlgebraKiT CMS is used.
 */
function createSession($exerciseId, $majorVersion) {
    $apiKey = get_option('akit_api_key');

    if (empty($apiKey)) {
        $sess = new SessionResponse();
        $sess->success = false;
        $sess->msg = 'No API Key is set. Go to the settings for the AlgebraKiT plugin to enter an API Key.';
        $sess->sessions = [];
        return [
            $sess
        ];
    }
    $exList = [
        0 => new ExerciseDef($exerciseId, $majorVersion)
    ];
    $data = new CreateSessionRequestBody($exList);
    
    return akitPost('/session/create', $data, $apiKey, $apiKey);
}

function render_akit($attributes) {
    $exerciseId = $attributes['exerciseId'];
    $majorVersion = $attributes['majorVersion'];
    $solutionMode = $attributes['solutionMode'];
    
    //First, create the session
    $session = createSession($exerciseId, $majorVersion);

    //Wrap the response in a <p> tag so the exercise is displayed in a paragraph block
    $html = "<p>";
    for ($ii = 0; $ii < count($session); $ii++) {
        $ex = $session[$ii];
        //The AlgebraKiT API supports creation of multiple sessions for multiple exercises.
        //The response is always an array of response objects, each containing an array of sessions.
        //In this case, both arrays are singletons, but we iterate over them anyway so this code is easily extensible when adding support for multiple exercises/sessions
        if($ex->success) {
            for($nn=0; $nn < count($ex->sessions); $nn++) {
                // The session object contains an html property which can be directly inserted into the page, automatically rendering the widget
                $sessionId = $ex->sessions[$nn]->sessionId;
                $solutionModeAttr = $solutionMode
                    ? ' solution-mode'
                    : '';
                $html .= "<akit-exercise session-id='$sessionId'$solutionModeAttr></akit-exercise>";
            }
        } else if ($ex != null) {
            $html .= "Failed to generate session for exercise";
            if (isset($ex->msg)) {
                $html.= ": ".$ex->msg;
            }
            else if (isset($ex->sessions) && isset($ex->sessions[0]) && isset($ex->sessions[0]->msg)) {
                $html.= ": ".$ex->sessions[0]->msg;
            }
            else {
                $html.= ".";
            }
        }
    } 
    $html .= "</p>";
    //Add the scripts necessary for the widget to function
    $script = file_get_contents(plugin_dir_path( __FILE__ ) . 'widgetLoader.js');
    $html .= "<link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/katex@0.10.1/dist/katex.min.css'></script>";
    $html .= "<script src='https://cdn.jsdelivr.net/npm/katex@0.10.1/dist/katex.min.js'></script>";
    $html .= "
    <script>
        $script
    </script>
    ";
    return $html;
}


//Wordpress editor block
function akit_register_block() {
    wp_register_script(
        'akit-block',
        plugins_url( 'akit-block.js', __FILE__ ),
        array( 
            'wp-blocks',
            'wp-element',
            'wp-editor'
        ),
        filemtime( plugin_dir_path( __FILE__ ) . 'akit-block.js' )
    );

    wp_register_style(
        'akit-block-editor-style',
        plugins_url( 'editor.css', __FILE__ ),
        array( 'wp-edit-blocks' ),
        filemtime( plugin_dir_path( __FILE__ ) . 'editor.css' )
    );
 
    register_block_type( 'algebrakit/algebrakit-exercise', array(
        'editor_style' => 'akit-block-editor-style',
        'editor_script' => 'akit-block',
        'attributes'      => array(
            'exerciseId'    => array(
                'type'      => 'string',
            ),
            'majorVersion' => array(
                'type'      => 'string',
            ),
            'solutionMode' => array(
                'type'      => 'boolean',
            ),
        ),
        'render_callback' => 'render_akit',
    ) );
}

//Wordpress plugin settings
function algebrakit_register_settings() {
    add_option( 'akit_api_key', '');
    register_setting( 'algebrakit_options_group', 'akit_api_key' );
}

function algebrakit_options_page() {
?>
    <div>
        <?php screen_icon(); ?>
        <h2>AlgebraKiT Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields( 'algebrakit_options_group' ); ?>
            <p>Here you can set the global settings for the AlgebraKiT plugin</p>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="akit_api_key">API Key</label></th>
                    <td><input type="text" id="akit_api_key" name="akit_api_key" value="<?php echo get_option('akit_api_key'); ?>" /></td>
                </tr>
            </table>
            <?php  submit_button(); ?>
        </form>
    </div>
<?php
}

function algebrakit_register_options_page() {
    add_options_page('AlgebraKiT Settings', 'AlgebraKiT', 'manage_options', 'algebrakit', 'algebrakit_options_page');
}

add_action( 'init', 'akit_register_block' );
add_action( 'admin_init', 'algebrakit_register_settings' );
add_action('admin_menu', 'algebrakit_register_options_page');
