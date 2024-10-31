<?php

/**
 * Plugin Name:       Gateway Addon for Swish Payment for GiveWP
 * Plugin URI:        https://proloybhaduri.com/
 * Description:       Allows accepting donations on <strong> GiveWP</strong> WordPress donation plugin. using <strong>Swish App</strong>.
 * Version:           1.0.2
 * Requires at least: 5.2
 * Requires PHP:      8.1
 * Author:            Proloy Bhaduri
 * Author URI:        https://proloybhaduri.com
 * Text Domain:       giveswish
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

//direct access prohibited 
defined('ABSPATH') or die('Not with us ');
define('GIVESWISH_PLUGIN_FILE', __FILE__);
define('GIVESWISH_PLUGIN_DIR', plugin_dir_path(GIVESWISH_PLUGIN_FILE));
define('GIVESWISH_PLUGIN_URL', plugin_dir_url(GIVESWISH_PLUGIN_FILE));
define('GIVESWISH_PLUGIN_BASENAME', plugin_basename(GIVESWISH_PLUGIN_FILE));

define('GIVESWISH_PLUGIN_VERSION', '1.0.2');

require_once  GIVESWISH_PLUGIN_DIR . 'vendor/autoload.php';
new GiveSwish\Payment\GiveSwish();
