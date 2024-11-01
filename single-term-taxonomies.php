<?php

/**
	Plugin Name: Single-Term Taxonomies
	Plugin URI: http://wordpress.org/plugins/single-term-taxonomies/
	Description: Using the "Single-Term Taxonomies" plugin you can limit the taxonomy terms chosen for a post to maximum one. <strong>If you like this plugin, please consider a <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=8DAVXJ35WQSRE" target="_blank">donation</a>.</strong>
	Version: 1.0.2
	Author: ShadowsDweller
	Author URI: http://htmlexpert.net/
*/

defined('ABSPATH') or die();

add_action('init', 'stt_init');
function stt_init() {
	require_once('includes/class-single-term-taxonomies.php');

	new Single_Term_Taxonomies;
}