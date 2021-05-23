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
    $exId = $atts['exercise-id'];

    $exTag = addExerciseRef($exId, $exVersion, false);
    return $exTag; // "<akit-exercise cached-ref="..."></akit-exercise>";
}


add_shortcode('akit-exercise', 'akit_exercise_shortcode'); 
