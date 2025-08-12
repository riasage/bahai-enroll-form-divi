<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

class BEFD_Utils {

    public static function sanitize_text( $value ) {
        return sanitize_text_field( $value );
    }

    public static function sanitize_email( $value ) {
        return sanitize_email( $value );
    }

    public static function encrypt( $plaintext, $passphrase ) {
        if ( ! $passphrase ) return false;
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($plaintext, 'aes-256-cbc', $passphrase, OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $passphrase, true);
        return base64_encode( $iv . $hmac . $ciphertext_raw );
    }

    public static function decrypt( $ciphertext, $passphrase ) {
        $c = base64_decode($ciphertext);
        $ivlen = openssl_cipher_iv_length('aes-256-cbc');
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, 32);
        $ciphertext_raw = substr($c, $ivlen + 32);
        $original = openssl_decrypt($ciphertext_raw, 'aes-256-cbc', $passphrase, OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $passphrase, true);
        if ( hash_equals($hmac, $calcmac) ) return $original;
        return false;
    }

}
