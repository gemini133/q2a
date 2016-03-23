<?php
/*
	Plugin Name: RankGold
	Plugin URI:
	Plugin Description: RankGold client for q2a
	Plugin Version: 1.0
	Plugin Date: 2011-03-27
	Plugin Author: RankGold
	Plugin Author URI: http://members.rankgold.com
	Plugin License: GPLv2
	Plugin Minimum Question2Answer Version: 1.4
	Plugin Update Check URI:
*/


if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
	header('Location: ../../');
	exit;
}

qa_register_plugin_module(
	'process', // type of module
	'plugin.php', // PHP file containing module class
	'RankGold', // module class name in that PHP file
	'rank gold' // human-readable name of module
);
