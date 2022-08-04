<?php
/**
 * Plugin Name: AlgebraKiT
 * Plugin URI: https://docs.algebrakit-learning.com/plugins/wordpress/
 * Description: Running AlgebraKiT Exercises on your wordpress website
 * Requires PHP: 7.2
 */

$HOST = "https://api.algebrakit.com";
$WIDGET_HOST = "https://widgets.algebrakit.com";
$THEME = get_option("akit_theme");
if($THEME==null) $THEME="akit";
$API_KEY = get_option('akit_api_key'); 


include('types.php');                             // general types and functionality
include('create-sessions.php');                   // general types and functionality
include('settings.php');                          // defines settings page
include('gutenberg-plugin/gutenberg-plugin.php'); // defines the AlgebraKiT component for the Gutenberg editor
include('shortcode-plugin/shortcode.php');        // defines a shortcode for AlgebraKiT for all other Wordpress editors
include('endpoints.php');



wp_register_style(
    'akit-general-style',
    plugins_url( 'widgetLoader.css', __FILE__ ),
    null,
    'v1' );
wp_enqueue_style( 'akit-general-style' );
