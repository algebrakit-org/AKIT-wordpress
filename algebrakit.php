<?php
/**
 * Plugin Name: AlgebraKiT
 * Plugin URI: https://docs.algebrakit-learning.com/plugins/wordpress/
 * Description: Running AlgebraKiT Exercises on your wordpress website
 * Requires PHP: 7.2
 */


include('general.php');                           // general types and functionality
include('settings.php');                          // defines settings page
include('gutenberg-plugin/gutenberg-plugin.php'); // defines the AlgebraKiT component for the Gutenberg editor
include('shortcode-plugin/shortcode.php');        // defines a shortcode for AlgebraKiT for all other Wordpress editors

