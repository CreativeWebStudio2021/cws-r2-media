<?php
/**
 * Plugin Name: CWS R2 Media
 * Plugin URI: https://github.com/CreativeWebStudio/cws-r2-media
 * Description: Offload WordPress media to Cloudflare R2 with automatic synchronization and URL rewriting.
 * Version: 0.1.0
 * Requires at least: 6.5
 * Requires PHP: 8.1
 * Author: Creative Web Studio
 * Author URI: https://www.cwstudio.it
 * Text Domain: cws-r2-media
 */

defined('ABSPATH') || exit;

define('CWS_R2_MEDIA_VERSION', '0.1.0');

define('CWS_R2_MEDIA_PATH', plugin_dir_path(__FILE__));

define('CWS_R2_MEDIA_URL', plugin_dir_url(__FILE__));

require_once CWS_R2_MEDIA_PATH . 'includes/bootstrap.php';