<?php
/**
 * Plugin Name: Bahá'í Enrollment Form - Divi
 * Description: Divi Builder module providing a bilingual Bahá’í enrollment form.
 * Version: 1.0.0
 * Author: dev_g
 * Author URI: https://devg.grlpro.com
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define plugin constants.
define( 'BEFD_PATH', plugin_dir_path( __FILE__ ) );
define( 'BEFD_URL', plugin_dir_url( __FILE__ ) );

// Autoload or require plugin files.
require_once BEFD_PATH . 'includes/class-befd-admin.php';
require_once BEFD_PATH . 'includes/class-befd-form.php';
require_once BEFD_PATH . 'includes/modules/class-befd-divi-module.php';

// GitHub updates.
require_once BEFD_PATH . 'includes/plugin-update-checker/plugin-update-checker.php';
$befd_updater = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/riasage/bahai-enroll-form-divi',
    __FILE__,
    'bahai-enroll-form-divi'
);
$befd_updater->setBranch('main');
$befd_updater->getVcsApi()->enableReleaseAssets();
// Optional: For private repos, set your token
// $befd_updater->setAuthentication('YOUR_GITHUB_TOKEN');

// Plugin init hooks.
add_action( 'plugins_loaded', 'befd_init_plugin' );

function befd_init_plugin() {
    // Init your classes/modules here.
}
