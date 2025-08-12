<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BEFD_Divi_Module extends ET_Builder_Module {
    public $slug       = 'befd_enrollment_form';
    public $vb_support = 'on'; // support Visual Builder preview

    function init() {
        $this->name = esc_html__( 'Bahá’í Enrollment Form', 'bahai-enroll-form-divi' );
        $this->settings_modal_toggles = array(
            'general'  => array(
                'toggles' => array(
                    'content' => esc_html__( 'Content', 'bahai-enroll-form-divi' ),
                    'texts'   => esc_html__( 'Texts', 'bahai-enroll-form-divi' ),
                    'style'   => esc_html__( 'Style', 'bahai-enroll-form-divi' ),
                ),
            ),
        );
    }

    function get_fields() {
        $text_fields = array(
            'title_en'     => 'Title (EN)',
            'title_ja'     => 'Title (JA)',
            'preamble_en'  => 'Preamble (EN)',
            'preamble_ja'  => 'Preamble (JA)',
            'name_en'      => 'Name label (EN)',
            'name_ja'      => 'Name label (JA)',
            'address_en'   => 'Address label (EN)',
            'address_ja'   => 'Address label (JA)',
            'dob_en'       => 'Date of Birth (EN)',
            'dob_ja'       => 'Date of Birth (JA)',
            'gender_en'    => 'Gender (EN)',
            'gender_ja'    => 'Gender (JA)',
            'phone_en'     => 'Phone (EN)',
            'phone_ja'     => 'Phone (JA)',
            'email_en'     => 'Email (EN)',
            'email_ja'     => 'Email (JA)',
            'agree_en'     => 'Agree text (EN)',
            'agree_ja'     => 'Agree text (JA)',
            'send_en'      => 'Send button (EN)',
            'send_ja'      => 'Send button (JA)',
        );

        $fields = array(
            'language' => array(
                'label'       => esc_html__( 'Initial Language', 'bahai-enroll-form-divi' ),
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
            'button_bg' => array(
                'label'       => esc_html__( 'Send Button Enabled Color', 'bahai-enroll-form-divi' ),
                'type'        => 'color-alpha',
                'default'     => '#007cff',
                'toggle_slug' => 'style',
            ),
        );

        foreach ( $text_fields as $key => $label ) {
            $fields[ $key ] = array(
                'label'       => esc_html__( $label, 'bahai-enroll-form-divi' ),
                'type'        => 'text',
                'toggle_slug' => 'texts',
            );
        }

        return $fields;
    }

    function render( $attrs, $content = null, $render_slug = '' ) {
        // Defaults from plugin, then module overrides:
        $defaults  = BEFD_Form::texts();
        $overrides = array();
        foreach ( $this->props as $k => $v ) {
            if ( $v !== '' && isset( $defaults[$k] ) ) {
                $overrides[$k] = $v;
            }
        }
        $texts = wp_parse_args( $overrides, $defaults );

        $lang        = ( $this->props['language'] === 'ja' ) ? 'ja' : 'en';
        $show_toggle = $this->props['show_toggle'] === 'on';
        $button_bg   = ! empty( $this->props['button_bg'] ) ? $this->props['button_bg'] : '#007cff';

        ob_start();

        // Button color when enabled (agree checked)
        echo '<style>.befd-form-wrap .befd-send.enabled{background:' . esc_attr( $button_bg ) . ';color:#fff}</style>';
        if ( ! $show_toggle ) {
            echo '<style>.befd-lang-toggle{display:none !important}</style>';
        }

        // Provide $texts and $lang to the view and force initial language
        $_GET['lang'] = $lang;
        if ( file_exists( BEFD_PATH . 'views/form.php' ) ) {
            $view_texts = $texts;
            $texts = $view_texts;
            include BEFD_PATH . 'views/form.php';
        } else {
            echo '<p>' . esc_html__( 'Form template not found.', 'bahai-enroll-form-divi' ) . '</p>';
        }

        return ob_get_clean();
    }
}
