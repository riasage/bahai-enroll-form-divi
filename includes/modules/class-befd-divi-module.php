<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BEFD_Divi_Module extends ET_Builder_Module {
    public $slug       = 'befd_enrollment_form';
    public $vb_support = 'off';

    function init() {
        $this->name = esc_html__( 'Bahá’í Enrollment Form', 'bahai-enroll-form-divi' );
        $this->settings_modal_toggles = array(
            'general'  => array(
                'toggles' => array(
                    'content' => esc_html__( 'Content', 'bahai-enroll-form-divi' ),
                ),
            ),
            'advanced' => array(
                'toggles' => array(
                    'text' => esc_html__( 'Texts', 'bahai-enroll-form-divi' ),
                ),
            ),
        );
    }

    function get_fields() {
        return array(
            'language' => array(
                'label'       => esc_html__( 'Language', 'bahai-enroll-form-divi' ),
                'type'        => 'select',
                'options'     => array( 'en' => 'English', 'ja' => '日本語' ),
                'default'     => 'en',
                'toggle_slug' => 'content',
            ),
            'show_toggle' => array(
                'label'       => esc_html__( 'Show Language Toggle', 'bahai-enroll-form-divi' ),
                'type'        => 'yes_no_button',
                'options'     => array( 'on'=> 'Yes', 'off'=>'No' ),
                'default'     => 'on',
                'toggle_slug' => 'content',
            ),
        );
    }

    function render( $attrs, $content = null, $render_slug ) {
        return do_shortcode('[bahai_enrollment_form]');
    }
}
