<?php

/** shortcode to insert a single exercise. For efficiency, the exercise is not created immediately, but the 
 *  reference to the exercise is stored in a map. 
 *  Function init_sessions() will be called once to create sessions for all exercises in the map in a single 
 *  call to AlgebraKiT. */
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
    if (array_key_exists('solution-mode', $atts)) {
        $exSolution = $atts['solution-mode']=='true';
    }
    else {
        $exSolution = false;
    }

    $exId = $atts['exercise-id'];

    $exTag = addExerciseRef($exId, $exVersion, $exSolution);
    return $exTag; // "<akit-exercise cached-ref="..."></akit-exercise>";
}

/** shortcode to insert a single interaction. For efficiency, the exercise is not created immediately, but the 
 *  reference to the exercise is stored in a map. 
 *  Function init_sessions() will be called once to create sessions for all exercises in the map in a single 
 *  call to AlgebraKiT. */
function akit_interaction_shortcode($atts = array()) {
    if (!array_key_exists('exercise-id', $atts)) {
        return;
    }
    if (!array_key_exists('ref-id', $atts)) {
        return;
    }

    if (array_key_exists('version', $atts)) {
        $exVersion = $atts['version'];
    }
    else {
        $exVersion = 'latest';
    }
    if (array_key_exists('solution-mode', $atts)) {
        $exSolution = $atts['solution-mode']=='true';
    }
    else {
        $exSolution = false;
    }

    $exId = $atts['exercise-id'];
    $refId= $atts['ref-id'];
    
    $exTag = addInteractionRef($exId, $refId, $exVersion, $exSolution);
    return $exTag; // "<akit-interaction cached-ref="..." ref-id=".."></akit-interaction>";
}

add_shortcode('akit-exercise', 'akit_exercise_shortcode'); 
add_shortcode('akit-interaction', 'akit_interaction_shortcode'); 
