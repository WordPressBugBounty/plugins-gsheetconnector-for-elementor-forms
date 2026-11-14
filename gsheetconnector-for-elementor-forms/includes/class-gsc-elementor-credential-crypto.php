<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Encrypts stored Google credentials (OAuth token, client secret).
 *
 * Mirrors the CF7 GSheetConnector Pro implementation (AES-256-GCM keyed from
 * the site's auth salt). Plaintext written by older versions is passed through
 * unchanged by decrypt(), so reads are safe before and after migration.
 *
 * @since 1.3.9
 */
class GSC_Elementor_Credential_Crypto
{
    const PREFIX = 'gselefenc:v1:';

    /**
     * Encrypts a secret value. Empty / non-string values and servers without
     * OpenSSL return the value unchanged.
     *
     * @param string $value Raw credential value.
     * @return string
     */
    public static function encrypt($value)
    {
        if (!is_string($value) || '' === $value) {
            return $value;
        }

        if (!self::is_available()) {
            return $value;
        }

        $key = hash('sha256', wp_salt('auth'), true);
        $iv  = openssl_random_pseudo_bytes(12);
        $tag = '';

        $ciphertext = openssl_encrypt($value, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        if (false === $ciphertext) {
            return $value;
        }

        return self::PREFIX . base64_encode($iv . $tag . $ciphertext);
    }

    /**
     * Decrypts a value produced by encrypt(). Plaintext is returned unchanged.
     *
     * @param mixed $value Stored option value.
     * @return mixed
     */
    public static function decrypt($value)
    {
        if (!is_string($value) || '' === $value) {
            return $value;
        }

        if (0 !== strpos($value, self::PREFIX)) {
            return $value;
        }

        if (!function_exists('openssl_decrypt')) {
            return '';
        }

        $decoded = base64_decode(substr($value, strlen(self::PREFIX)), true);

        if (false === $decoded || strlen($decoded) < 28) {
            return '';
        }

        $key        = hash('sha256', wp_salt('auth'), true);
        $iv         = substr($decoded, 0, 12);
        $tag        = substr($decoded, 12, 16);
        $ciphertext = substr($decoded, 28);

        $plain = openssl_decrypt($ciphertext, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);

        return false === $plain ? '' : $plain;
    }

    /**
     * Whether a stored value uses the encrypted wrapper.
     *
     * @param mixed $value Stored option value.
     * @return bool
     */
    public static function is_encrypted($value)
    {
        return is_string($value) && 0 === strpos($value, self::PREFIX);
    }

    /**
     * Whether OpenSSL can encrypt on this server.
     *
     * @return bool
     */
    public static function is_available()
    {
        return function_exists('openssl_encrypt')
            && function_exists('openssl_decrypt')
            && function_exists('openssl_random_pseudo_bytes');
    }

    /**
     * Admin notice on the plugin page when OpenSSL is missing (credentials
     * stay plaintext; the plugin keeps working).
     *
     * @return void
     */
    public static function maybe_show_admin_notice()
    {
        if (self::is_available() || !is_admin() || !current_user_can('manage_options')) {
            return;
        }

        // phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only page-slug check gating an admin notice.
        if (!isset($_GET['page']) || 'gsheetconnector-elementor-config' !== sanitize_text_field(wp_unslash($_GET['page']))) {
            return;
        }

        if (class_exists('GsEl_Connector_Utility')) {
            echo wp_kses_post(
                GsEl_Connector_Utility::instance()->admin_notice(
                    array(
                        'type'    => 'error',
                        'message' => __(
                            'Google Sheet Connector: your saved Google credentials are currently stored without encryption because the PHP OpenSSL extension is not enabled on this server. The plugin will keep working, but please ask your hosting provider to enable OpenSSL so these credentials can be encrypted at rest.',
                            'gsheetconnector-for-elementor-forms'
                        ),
                    )
                )
            );
        }
    }
}
add_action('admin_notices', array('GSC_Elementor_Credential_Crypto', 'maybe_show_admin_notice'));
