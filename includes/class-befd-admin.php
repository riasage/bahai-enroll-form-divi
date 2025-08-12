<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BEFD_Admin {

    public static function get_settings() {
        $defaults = array(
            'recipient_email' => get_option('admin_email'),
            'confirm_message' => __( 'Thank you. Your application has been received.', 'bahai-enroll-form-divi' ),
            'recaptcha_site_key' => '',
            'recaptcha_secret_key' => '',
            'keep_encrypted_copies' => '0',
            'encryption_passphrase' => '',
            'module_texts' => array(),
        );
        $opt = get_option( 'befd_settings', array() );
        return wp_parse_args( $opt, $defaults );
    }

    public static function save_settings( $data ) {
        update_option( 'befd_settings', $data );
    }

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'menu' ) );
        add_action( 'admin_init', array( $this, 'handle_post' ) );
    }

    public function menu() {
        add_menu_page(
            __( 'Bahá’í Enrollment', 'bahai-enroll-form-divi' ),
            __( 'Bahá’í Enrollment', 'bahai-enroll-form-divi' ),
            'manage_options',
            'befd-settings',
            array( $this, 'render_page' ),
            'dashicons-welcome-learn-more'
        );
    }

    public function handle_post() {
        if ( ! current_user_can('manage_options') ) return;

        if ( isset($_POST['befd_settings_nonce']) && wp_verify_nonce( $_POST['befd_settings_nonce'], 'befd_save_settings' ) ) {
            $settings = self::get_settings();
            $settings['recipient_email'] = sanitize_email( isset($_POST['recipient_email']) ? $_POST['recipient_email'] : '' );
            $settings['confirm_message'] = wp_kses_post( isset($_POST['confirm_message']) ? $_POST['confirm_message'] : '' );
            $settings['recaptcha_site_key'] = sanitize_text_field( isset($_POST['recaptcha_site_key']) ? $_POST['recaptcha_site_key'] : '' );
            $settings['recaptcha_secret_key'] = sanitize_text_field( isset($_POST['recaptcha_secret_key']) ? $_POST['recaptcha_secret_key'] : '' );
            $settings['keep_encrypted_copies'] = isset($_POST['keep_encrypted_copies']) ? '1' : '0';
            $settings['encryption_passphrase'] = sanitize_text_field( isset($_POST['encryption_passphrase']) ? $_POST['encryption_passphrase'] : '' );
            $settings['module_texts'] = array();
            if ( isset($_POST['module_texts']) && is_array($_POST['module_texts']) ) {
                foreach ( $_POST['module_texts'] as $k => $v ) {
                    $settings['module_texts'][$k] = sanitize_text_field( $v );
                }
            }
            self::save_settings( $settings );
            add_settings_error( 'befd_messages', 'befd_saved', __( 'Settings saved.', 'bahai-enroll-form-divi' ), 'updated' );
        }

        if ( isset($_GET['befd_export']) && $_GET['befd_export'] === '1' ) {
            $this->export_csv();
        }
    }

    public function render_page() {
        $settings = self::get_settings();
        ?>
        <div class="wrap">
          <h1>Bahá’í Enrollment – <?php esc_html_e('Settings', 'bahai-enroll-form-divi'); ?></h1>
          <?php settings_errors( 'befd_messages' ); ?>
          <h2 class="nav-tab-wrapper">
            <a href="#dashboard" class="nav-tab nav-tab-active">Dashboard</a>
            <a href="#email" class="nav-tab">Email & reCAPTCHA</a>
            <a href="#module" class="nav-tab">Module Texts</a>
            <a href="#export" class="nav-tab">Export</a>
          </h2>

          <form method="post">
            <?php wp_nonce_field( 'befd_save_settings', 'befd_settings_nonce' ); ?>

            <div id="dashboard" class="tab-pane" style="display:block">
              <h2>Welcome</h2>
              <p>This plugin adds a bilingual (English/Japanese) Enrollment Form and a Divi Builder Module.</p>
              <p><strong>Shortcode:</strong> <code>[bahai_enrollment_form]</code></p>
              <p><strong>Version:</strong> <?php echo esc_html( BEFD_VERSION ); ?></p>
              <p>Use the tabs to configure email delivery, reCAPTCHA v3, and module texts.</p>
            </div>

            <div id="email" class="tab-pane" style="display:none">
              <h2>Email & reCAPTCHA</h2>
              <table class="form-table">
                <tr><th scope="row"><label for="recipient_email">Receiving Email</label></th>
                  <td><input name="recipient_email" type="email" id="recipient_email" value="<?php echo esc_attr($settings['recipient_email']); ?>" class="regular-text"></td></tr>
                <tr><th scope="row"><label for="confirm_message">Confirmation Message (shown after submit)</label></th>
                  <td><textarea name="confirm_message" id="confirm_message" rows="4" class="large-text"><?php echo esc_textarea($settings['confirm_message']); ?></textarea></td></tr>
                <tr><th scope="row"><label for="recaptcha_site_key">reCAPTCHA v3 Site Key</label></th>
                  <td><input name="recaptcha_site_key" id="recaptcha_site_key" type="text" value="<?php echo esc_attr($settings['recaptcha_site_key']); ?>" class="regular-text"></td></tr>
                <tr><th scope="row"><label for="recaptcha_secret_key">reCAPTCHA v3 Secret Key</label></th>
                  <td><input name="recaptcha_secret_key" id="recaptcha_secret_key" type="text" value="<?php echo esc_attr($settings['recaptcha_secret_key']); ?>" class="regular-text"></td></tr>
                <tr><th scope="row">Keep encrypted copies for export</th>
                  <td><label><input type="checkbox" name="keep_encrypted_copies" <?php checked($settings['keep_encrypted_copies'], '1'); ?>> Store an encrypted copy of each submission in uploads (optional).</label></td></tr>
                <tr><th scope="row"><label for="encryption_passphrase">Encryption Passphrase</label></th>
                  <td><input name="encryption_passphrase" id="encryption_passphrase" type="text" value="<?php echo esc_attr($settings['encryption_passphrase']); ?>" class="regular-text" placeholder="Set a strong passphrase"></td></tr>
              </table>
            </div>

            <div id="module" class="tab-pane" style="display:none">
              <h2>Module Texts</h2>
              <p>Override default texts (leave blank to use defaults).</p>
              <table class="form-table">
                <?php
                $fields = array('title_en','title_ja','preamble_en','preamble_ja','name_en','name_ja','address_en','address_ja','dob_en','dob_ja','gender_en','gender_ja','phone_en','phone_ja','email_en','email_ja','agree_en','agree_ja','send_en','send_ja');
                foreach ($fields as $f) {
                    $val = isset($settings['module_texts'][$f]) ? $settings['module_texts'][$f] : '';
                    echo '<tr><th scope="row"><label>' . esc_html($f) . '</label></th><td><input name="module_texts['.esc_attr($f).']" type="text" value="'.esc_attr($val).'" class="large-text" /></td></tr>';
                }
                ?>
              </table>
            </div>

            <div id="export" class="tab-pane" style="display:none">
              <h2>Export</h2>
              <p>Download a CSV of all encrypted submissions stored in uploads.</p>
              <p><a href="<?php echo esc_url( add_query_arg('befd_export','1') ); ?>" class="button button-primary">Export CSV</a></p>
            </div>

            <p class="submit"><button type="submit" class="button button-primary">Save Changes</button></p>
          </form>
        </div>
        <script>
        (function(){
          const tabs = document.querySelectorAll('.nav-tab'); 
          tabs.forEach(t => t.addEventListener('click', function(e){
            e.preventDefault();
            tabs.forEach(x=>x.classList.remove('nav-tab-active'));
            document.querySelectorAll('.tab-pane').forEach(p=>p.style.display='none');
            this.classList.add('nav-tab-active');
            document.querySelector(this.getAttribute('href')).style.display='block';
          }));
        })();
        </script>
        <?php
    }

    private function export_csv() {
        $settings = self::get_settings();
        $pass = $settings['encryption_passphrase'];
        $upload_dir = wp_upload_dir();
        $dir = trailingslashit( $upload_dir['basedir'] ) . 'befd-encrypted/';
        if ( ! file_exists( $dir ) ) {
            wp_die( 'No submissions found.' );
        }

        $rows = array();
        foreach ( glob( $dir . '*.json' ) as $file ) {
            $blob = file_get_contents( $file );
            $json = BEFD_Utils::decrypt( $blob, $pass );
            if ( $json ) {
                $data = json_decode( $json, true );
                $rows[] = $data;
            }
        }

        if ( empty( $rows ) ) {
            wp_die( 'No decryptable submissions found. Check your passphrase.' );
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=bahai-enrollment-export.csv');
        $out = fopen('php://output', 'w');
        fputcsv($out, array('timestamp','lang','name','address','dob','gender','phone','email'));
        foreach ($rows as $r) {
            fputcsv($out, array( $r['timestamp'], $r['lang'], $r['name'], $r['address'], $r['dob'], $r['gender'], $r['phone'], $r['email'] ) );
        }
        fclose($out);
        exit;
    }
}
new BEFD_Admin();
