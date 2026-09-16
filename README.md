# EnvoiSMS WooCommerce & WordPress Plugin

Official WooCommerce & WordPress plugin for [EnvoiSMS.ma](https://envoisms.ma) — [Passerelle SMS & WhatsApp Business](https://envoisms.ma/fr/docs) and [API SMS Maroc](https://envoisms.ma/fr/tarifs).

Automate customer order notifications, OTP verifications, and shipping alerts via SMS and WhatsApp Business for Moroccan e-commerce stores.

For full API documentation, visit the [Passerelle SMS & WhatsApp Business](https://envoisms.ma/fr/docs). For pricing plans and credit packs, visit [API SMS Maroc](https://envoisms.ma/fr/tarifs).

---

## Features

- **SMS Order Notifications**: Send order updates by SMS through Morocco's operator routes — it reaches every customer, WhatsApp users included, with no extra setup. (WhatsApp Business sends need your own connected number and an approved template; use the [EnvoiSMS.ma dashboard](https://envoisms.ma/fr/whatsapp/) or the API for those.)
- **Moroccan Carrier Normalisation**: Automatic formatting and validation of Moroccan phone numbers (+212 6 / +212 7).
- **Branded Sender ID**: Broadcast messages using your official registered business sender name.
- **Order Lifecycle Events**: Trigger alerts on New Order, Processing, Completed, and Cancelled statuses.
- **Dynamic Variables**: Personalize messages with `{first_name}`, `{order_id}`, `{total}`, and `{status}`.
- **Zero Heavy Dependencies**: Built natively using WordPress HTTP API (`wp_remote_post`).

---

## Installation

1. Download the plugin zip or clone this repository into `/wp-content/plugins/envoisms-woocommerce`.
2. In WordPress Admin, navigate to **Plugins** and click **Activate** for **EnvoiSMS.ma – SMS & WhatsApp for WooCommerce**.
3. Go to **Settings → EnvoiSMS.ma**.
4. Enter your API Key from your [EnvoiSMS.ma Dashboard](https://envoisms.ma/dashboard).
5. Configure your sender name and the message templates for each order status.
6. Click **Save Changes**.

---

## Documentation & Tarifs

- Documentation technique & intégration : [Passerelle SMS & WhatsApp Business](https://envoisms.ma/fr/docs)
- Grille tarifaire et packs de crédits : [API SMS Maroc](https://envoisms.ma/fr/tarifs)

---

## License

MIT © [EnvoiSMS.ma](https://envoisms.ma)
