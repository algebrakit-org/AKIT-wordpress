<?php

/** shortcode to insert the algebrahub search bar. */
function ahub_search_shortcode($atts = array()) {
    $objType = 'exercise';
    if (array_key_exists('type', $atts)) {
        $objType = $attr['type'];
    }


    $tag = '<iframe src="https://app.algebrahub.com/assets/search.html" style="width: 100%" height="68"></iframe>';

    return $tag;
}

add_shortcode('ahub-search', 'ahub_search_shortcode'); 

?>