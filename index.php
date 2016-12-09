<?php
/**
 * Front to the Mtaandao application. This file doesn't do anything, but loads
 * main-header.php which does and tells Mtaandao to load the theme.
 *
 * @package Mtaandao
 */

/**
 * Tells Mtaandao to load the Mtaandao theme and output it.
 *
 * @var bool
 */
define('MN_USE_THEMES', true);

/** Loads the Mtaandao Environment and Template */
require( dirname( __FILE__ ) . '/main-header.php' );
