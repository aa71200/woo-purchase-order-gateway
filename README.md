# B2B Purchase Order Gateway

**What to try.** Try checkout as an approved business customer, then verify the saved reference on the on-hold order.

[![WordPress and WooCommerce integration checks](https://github.com/aa71200/woo-purchase-order-gateway/actions/workflows/checks.yml/badge.svg?branch=main)](https://github.com/aa71200/woo-purchase-order-gateway/actions/workflows/checks.yml)

Approved business customers need to submit a purchase order reference at checkout and leave the order on hold for review. This CodexaWP sample implements that offline workflow with WooCommerce's gateway API.

## Checkout previews

<p align="center">
  <a href="docs/checkout-block.png"><img src="docs/checkout-block.png" width="575" alt="Purchase order reference field in WooCommerce Block Checkout"></a>
  <br>
  <em>Block checkout · purchase order reference for an approved business account</em>
</p>

<details>
<summary>View Classic Checkout</summary>

<p align="center">
  <a href="docs/checkout-classic.png"><img src="docs/checkout-classic.png" width="575" alt="Purchase order reference field in WooCommerce Classic Checkout"></a>
  <br>
  <em>Classic checkout · the same purchase order reference flow</em>
</p>

</details>

## Try it

Enable **Purchase Order** under WooCommerce payment settings. Open a test customer's profile under **Users** and allow Purchase Order payment. Administrators who manage WooCommerce and can edit that customer can grant or revoke approval. The reference accepts 3–40 letters, digits, hyphens or slashes.

## Verification

Activate this plugin on a disposable WordPress site with WooCommerce active and `WP_ENVIRONMENT_TYPE` set to `local`. Set `CODEXAWP_TEST_WP_LOAD` to the absolute path to that site's `wp-load.php`, then run `php tests/check.php` from this repository.

The PHP fixture checks qualification, reference format, review status and the saved reference. The shared browser fixture runs both checkout interfaces for guest, unapproved and approved accounts, then checks the persisted on-hold order and purchase order reference.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).
