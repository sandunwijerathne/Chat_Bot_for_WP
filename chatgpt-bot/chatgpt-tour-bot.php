<?php
/*
Plugin Name: ChatGPT Tour Bot
Plugin URI: https://github.com/yourusername/chatgpt-tour-bot
Description: A chatbot plugin for WordPress that integrates with ChatGPT and uses site content to answer visitor questions.
Version: 1.0
Author: IdealWebKit 
Author URI: https://idealwebkit.com/
License: GPLv2 or later
*/

// Exit if accessed directly
defined('ABSPATH') || exit;

// Define constants
define('CHATGPT_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('CHATGPT_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include files
require_once CHATGPT_PLUGIN_DIR . 'includes/chatgpt-settings.php';
require_once CHATGPT_PLUGIN_DIR . 'includes/chatgpt-api.php';
require_once CHATGPT_PLUGIN_DIR . 'includes/chatgpt-shortcode.php';

// Enqueue assets
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('chatgpt-style', CHATGPT_PLUGIN_URL . 'assets/chatgpt.css');
    wp_enqueue_script('chatgpt-script', CHATGPT_PLUGIN_URL . 'assets/chatgpt.js', ['jquery'], null, true);
    wp_localize_script('chatgpt-script', 'chatgptAjax', [
        'ajaxUrl' => site_url('/wp-json/custom-chatgpt/v1/ask'),
    ]);
});
