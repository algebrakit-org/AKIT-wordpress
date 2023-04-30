<?php

function render_akit($attributes) {

    $exerciseId = $attributes['exerciseId'];
    $majorVersion = $attributes['majorVersion'];
    $solutionMode = $attributes['solutionMode'];
    $solutionButton = $attributes['solutionButton'];
    
    $exTag = addExerciseRef($exerciseId, $majorVersion, $solutionMode, $solutionButton); // "<akit-exercise cached-ref="..."></akit-exercise>";

    return $exTag;
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
            'solutionButton' => array(
                'type'      => 'boolean',
            ),
        ),
        'render_callback' => 'render_akit',
    ) );
}


add_action( 'init', 'akit_register_block' );
