<?php
/*
Plugin Name: Avilio
Plugin URI: https://yourwebsite.com/plugin
Description: A brief description of your plugin
Version: 1.0.0
Author: Your Name
Author URI: https://yourwebsite.com
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Use Composer autoloader
require_once plugin_dir_path(__FILE__) . '../vendor/autoload.php';

// Initialize your plugin
add_action('plugins_loaded', function() {
    // Your initialization code here
    new \Avilio\Theme();
});