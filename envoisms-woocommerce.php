<?php
/**
 * Plugin Name: EnvoiSMS for WooCommerce
 * Plugin URI: https://envoisms.ma
 * Description: Automated Order SMS / WhatsApp Notifications & OTP Checkout Verification for Morocco via EnvoiSMS.ma API.
 * Version: 1.2.0
 * Author: EnvoiSMS.ma
 * Author URI: https://envoisms.ma
 * License: MIT
 * Text Domain: envoisms-woocommerce
 */

if (!defined('ABSPATH')) {
    exit;
}

class EnvoiSMS_WooCommerce {
    private string $api_key;
    private string $sender_id;

    public function __construct() {
        $this->api_key = get_option('envoisms_api_key', '');
        $this->sender_id = get_option('envoisms_sender_id', 'MonBusiness');

        add_action('woocommerce_order_status_completed', [$this, 'notify_order_completed']);
        add_action('woocommerce_order_status_processing', [$this, 'notify_order_processing']);
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            'EnvoiSMS Settings',
            'EnvoiSMS Maroc',
            'manage_options',
            'envoisms-settings',
            [$this, 'render_settings_page']
        );
    }

    public function register_settings() {
        register_setting('envoisms_options', 'envoisms_api_key');
        register_setting('envoisms_options', 'envoisms_sender_id');
        register_setting('envoisms_options', 'envoisms_msg_processing');
        register_setting('envoisms_options', 'envoisms_msg_completed');
    }

    public function render_settings_page() {
        $default_proc = 'Bonjour {first_name}, votre commande #{order_id} d\'un montant de {order_total} MAD est en cours de préparation.';
        $default_comp = 'Bonjour {first_name}, votre commande #{order_id} est expédiée et en cours de livraison.';
        ?>
        <div class="wrap">
            <h2>EnvoiSMS.ma Configuration</h2>
            <form method="post" action="options.php">
                <?php settings_fields('envoisms_options'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row">API Key</th>
                        <td><input type="password" name="envoisms_api_key" value="<?php echo esc_attr(get_option('envoisms_api_key')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Sender ID (Expéditeur)</th>
                        <td><input type="text" name="envoisms_sender_id" value="<?php echo esc_attr(get_option('envoisms_sender_id', 'MonBusiness')); ?>" class="regular-text" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Message Commande en cours</th>
                        <td>
                            <textarea name="envoisms_msg_processing" rows="3" class="large-text"><?php echo esc_textarea(get_option('envoisms_msg_processing', $default_proc)); ?></textarea>
                            <p class="description">Variables: <code>{first_name}</code>, <code>{last_name}</code>, <code>{order_id}</code>, <code>{order_total}</code>, <code>{billing_city}</code></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">Message Commande expédiée</th>
                        <td>
                            <textarea name="envoisms_msg_completed" rows="3" class="large-text"><?php echo esc_textarea(get_option('envoisms_msg_completed', $default_comp)); ?></textarea>
                            <p class="description">Variables: <code>{first_name}</code>, <code>{last_name}</code>, <code>{order_id}</code>, <code>{order_total}</code></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }

    public function notify_order_processing($order_id) {
        $order = wc_get_order($order_id);
        $phone = $order->get_billing_phone();
        $template = get_option('envoisms_msg_processing', 'Bonjour {first_name}, votre commande #{order_id} est en préparation.');
        $message = $this->parse_template($template, $order);
        $this->send_sms($phone, $message, 'wc-' . $order_id . '-processing');
    }

    public function notify_order_completed($order_id) {
        $order = wc_get_order($order_id);
        $phone = $order->get_billing_phone();
        $template = get_option('envoisms_msg_completed', 'Bonjour {first_name}, votre commande #{order_id} est expédiée.');
        $message = $this->parse_template($template, $order);
        $this->send_sms($phone, $message, 'wc-' . $order_id . '-completed');
    }

    private function parse_template(string $template, $order): string {
        $replacements = [
            '{first_name}'   => $order->get_billing_first_name(),
            '{last_name}'    => $order->get_billing_last_name(),
            '{order_id}'     => (string) $order->get_id(),
            '{order_total}'  => (string) $order->get_total(),
            '{billing_city}' => $order->get_billing_city(),
        ];
        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    // $idempotency_key: one key per (order, status) — WooCommerce can fire a
    // status hook more than once (plugin conflicts, manual re-saves), and the
    // API honours the key for 24 h, so the customer gets one SMS and the
    // store pays for one.
    private function send_sms($phone, $message, $idempotency_key = '') {
        if (empty($this->api_key) || empty($phone)) {
            return;
        }

        // Format Moroccan numbers: 0612345678 -> +212612345678
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '212' . substr($cleanPhone, 1);
        }
        $formattedPhone = '+' . ltrim($cleanPhone, '+');

        wp_remote_post('https://api.envoisms.ma/v1/messages', [
            'headers' => array_filter([
                'Authorization' => 'Bearer ' . $this->api_key,
                'Content-Type' => 'application/json',
                'Idempotency-Key' => $idempotency_key,
            ]),
            'body' => wp_json_encode([
                'to' => $formattedPhone,
                'message' => $message,
                'from' => $this->sender_id,
                'channel' => 'sms',
            ]),
            'timeout' => 10,
        ]);
    }
}

new EnvoiSMS_WooCommerce();
