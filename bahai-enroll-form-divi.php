<?php
/**
 * Plugin Name: Bahá’í Enrollment Form (Divi Builder Module)
 * Plugin URI: https://devg.grlpro.com
 * Description: Divi Builder module with bilingual (EN/JA) Bahá’í enrollment form, reCAPTCHA v3, and email delivery. Fully intergrated into DIVI. no shortcode needed
 * Version: 1.1.1
 * Author: dev_g
 * Author URI: https://devg.grlpro.com
 * Text Domain: bahai-enroll-form-divi
 * Domain Path: /languages
 * License: GPLv2 or later
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

define( 'BEFD_VERSION', '1.1.0' );
define( 'BEFD_SLUG', 'bahai-enroll-form-divi' );
define( 'BEFD_PATH', plugin_dir_path( __FILE__ ) );
define( 'BEFD_URL', plugin_dir_url( __FILE__ ) );

require_once BEFD_PATH . 'includes/class-befd-utils.php';
require_once BEFD_PATH . 'includes/class-befd-admin.php';
require_once BEFD_PATH . 'includes/class-befd-form.php';

 class BEFD_Divi_Module extends ET_Builder_Module {
     public $slug       = 'befd_enrollment_form';
-    public $vb_support = 'on';
+    public $vb_support = 'partial';

@@
-        // Defaults from plugin, then module overrides:
+        // Defaults from plugin, then module overrides:
         $defaults  = BEFD_Form::texts();
@@
-        $lang        = ( $this->props['language'] === 'ja' ) ? 'ja' : 'en';
+        // URL (?lang) takes priority; fall back to module's initial language
+        $initial_lang = ( $this->props['language'] === 'ja' ) ? 'ja' : 'en';
+        $url_lang     = ( isset($_GET['lang']) && in_array($_GET['lang'], array('en','ja'), true) ) ? $_GET['lang'] : null;
+        $lang         = $url_lang ? $url_lang : $initial_lang;
         $show_toggle = $this->props['show_toggle'] === 'on';
         $button_bg   = ! empty( $this->props['button_bg'] ) ? $this->props['button_bg'] : '#007cff';
@@
-        // Provide $texts and $lang to the view; force initial language for the render:
-        $_GET['lang'] = $lang;
+        // Provide $texts and selected $lang to the view (don’t touch $_GET)
+        $view_lang  = $lang;
+        $view_texts = $texts;
         if ( file_exists( BEFD_PATH . 'views/form.php' ) ) {
-            $view_texts = $texts;
-            $texts = $view_texts;
+            $texts = $view_texts; // expected by view
             include BEFD_PATH . 'views/form.php';


class BEFD_Plugin {
    public function __construct() {
        add_action( 'init', array( $this, 'i18n' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ) );

        // Register Divi module only when Divi is ready and present.
        add_action( 'et_builder_ready', function() {
            if ( class_exists( 'ET_Builder_Module' ) ) {
                require_once BEFD_PATH . 'includes/modules/class-befd-divi-module.php';
                new BEFD_Divi_Module();
            }
        } );
    }

    public function i18n() {
        load_plugin_textdomain( 'bahai-enroll-form-divi', false, dirname( plugin_basename(__FILE__) ) . '/languages' );
    }

    public function enqueue() {
        wp_enqueue_style( 'befd-style', BEFD_URL . 'assets/css/form.css', array(), BEFD_VERSION );
        wp_enqueue_script( 'befd-script', BEFD_URL . 'assets/js/form.js', array( 'jquery' ), BEFD_VERSION, true );

        $settings = BEFD_Admin::get_settings();
        wp_localize_script( 'befd-script', 'BEFD', array(
            'recaptchaSiteKey' => isset($settings['recaptcha_site_key']) ? $settings['recaptcha_site_key'] : '',
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'i18n' => array(
                'agree' => __( 'I AGREE that the personal information above will be used for administrative purposes such as membership records, distribution of communications, and statistics.', 'bahai-enroll-form-divi' ),
                'send'  => __( 'SEND', 'bahai-enroll-form-divi' ),
                'fillAnother' => __( 'Fill another form', 'bahai-enroll-form-divi' ),
                'submitted'   => __( 'Form filled in successfully.', 'bahai-enroll-form-divi' ),
            )
        ) );
    }
}
new BEFD_Plugin();
