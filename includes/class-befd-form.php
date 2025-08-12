<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BEFD_Form {

    public static function texts() {
        $s = BEFD_Admin::get_settings();
        $t = isset($s['module_texts']) ? $s['module_texts'] : array();
        $d = array(
          'title_en' => 'Application to enroll in the Bahá’í Community',
          'title_ja' => 'バハイ共同体加入申込書',
          'preamble_en' => 'I wish to enroll in the Bahá’í community. I accept Bahá’u’lláh as the Manifestation of God today. I understand that the Báb is His Forerunner, and that ‘Abdu’l-Bahá is His Successor, and that there are teachings and an administrative order to follow.',
          'preamble_ja' => '私はバハイ共同体への加入を希望します。バハオラを現代の神の顕示者として受けいれます。バブはその先駆者、アブドル・バハはその後継者であり、従うべき教えと行政秩序があることを理解しています。',
          'name_en' => 'Full Name',
          'name_ja' => '氏名',
          'address_en' => 'Address',
          'address_ja' => '住所',
          'dob_en' => 'Date Of Birth',
          'dob_ja' => '生年月日',
          'gender_en' => 'Gender',
          'gender_ja' => '性別',
          'phone_en' => 'Phone',
          'phone_ja' => '電話番号',
          'email_en' => 'Email',
          'email_ja' => 'メール・アドレス',
          'agree_en' => 'I AGREE that the personal information above will be used for administrative purposes such as membership records, distribution of communications, and statistics.',
          'agree_ja' => '上記の個人所情報は名簿管理、通信物配布、統計など行政的管理目的のみに用います。',
          'send_en' => 'SEND',
          'send_ja' => '送信'
        );
        return wp_parse_args( $t, $d );
    }

    public static function render_shortcode( $atts ) {
        ob_start();
        $texts = self::texts();

        $lang = ( isset($_GET['lang']) && $_GET['lang'] === 'ja' ) ? 'ja' : 'en';
        if ( isset($_GET['befd_success']) && $_GET['befd_success'] == '1' ) {
            $settings = BEFD_Admin::get_settings();
            echo '<div class="befd-confirmation"><p>' . wp_kses_post($settings['confirm_message']) . '</p>';
            echo '<p><a class="et_pb_button" href="' . esc_url( remove_query_arg('befd_success') ) . '">' . esc_html__( 'Fill another form', 'bahai-enroll-form-divi' ) . '</a></p></div>';
            return ob_get_clean();
        }

        include BEFD_PATH . 'views/form.php';
        return ob_get_clean();
    }

    public static function handle_submission() {
        if ( ! isset($_POST['befd_nonce']) || ! wp_verify_nonce( $_POST['befd_nonce'], 'befd_submit' ) ) {
            wp_die( 'Invalid request' );
        }

        $settings = BEFD_Admin::get_settings();

        // reCAPTCHA v3
        $token = isset($_POST['g-recaptcha-response']) ? $_POST['g-recaptcha-response'] : '';
        $ok = true;
        if ( ! empty( $settings['recaptcha_secret_key'] ) ) {
            $resp = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array( 'body' => array(
                'secret' => $settings['recaptcha_secret_key'],
                'response' => $token,
                'remoteip' => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : ''
            ) ) );
            if ( is_wp_error( $resp ) ) {
                $ok = false;
            } else {
                $body = json_decode( wp_remote_retrieve_body( $resp ), true );
                if ( empty($body['success']) || ( isset($body['score']) && floatval($body['score']) < 0.3 ) ) {
                    $ok = false;
                }
            }
        }
        if ( ! $ok ) {
            wp_die( 'reCAPTCHA failed.' );
        }

        $lang   = sanitize_text_field( isset($_POST['lang']) ? $_POST['lang'] : 'en' );
        $name   = BEFD_Utils::sanitize_text( isset($_POST['name']) ? $_POST['name'] : '' );
        $address= sanitize_textarea_field( isset($_POST['address']) ? $_POST['address'] : '' );
        $dob    = BEFD_Utils::sanitize_text( isset($_POST['dob']) ? $_POST['dob'] : '' );
        $gender = BEFD_Utils::sanitize_text( isset($_POST['gender']) ? $_POST['gender'] : '' );
        $phone  = BEFD_Utils::sanitize_text( isset($_POST['phone']) ? $_POST['phone'] : '' );
        $email  = BEFD_Utils::sanitize_email( isset($_POST['email']) ? $_POST['email'] : '' );

        $fields = compact('lang','name','address','dob','gender','phone','email');
        $fields['timestamp'] = current_time('mysql');

        // Email
        $to = $settings['recipient_email'];
        $subject = 'New Bahá’í Enrollment';
        $lines = array();
        foreach ( $fields as $k => $v ) { $lines[] = ucfirst($k) . ': ' . $v; }
        $body = implode( "\n", $lines );
        $headers = array( 'Content-Type: text/plain; charset=UTF-8' );
        wp_mail( $to, $subject, $body, $headers );

        // Optional encrypted copy
        if ( $settings['keep_encrypted_copies'] === '1' && ! empty($settings['encryption_passphrase']) ) {
            $upload_dir = wp_upload_dir();
            $dir = trailingslashit( $upload_dir['basedir'] ) . 'befd-encrypted/';
            if ( ! file_exists( $dir ) ) wp_mkdir_p( $dir );
            $blob = BEFD_Utils::encrypt( wp_json_encode($fields), $settings['encryption_passphrase'] );
            if ( $blob ) {
                file_put_contents( $dir . time() . '-' . wp_generate_password(8,false) . '.json', $blob );
            }
        }

        // redirect to confirmation
        wp_safe_redirect( add_query_arg( 'befd_success', '1', wp_get_referer() ? wp_get_referer() : home_url() ) );
        exit;
    }
}

add_action( 'admin_post_befd_submit', array( 'BEFD_Form', 'handle_submission' ) );
add_action( 'admin_post_nopriv_befd_submit', array( 'BEFD_Form', 'handle_submission' ) );
