<?php
declare(strict_types=1);

namespace CodexaWP\PurchaseOrder;
defined( 'ABSPATH' ) || exit;

final class Blocks extends \Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType {
	protected $name = ID;

	public function initialize(): void {
		$this->settings = get_option( 'woocommerce_' . ID . '_settings', [] );
	}

	public function is_active(): bool {
		return 'yes' === ( $this->settings['enabled'] ?? 'no' );
	}

	public function get_payment_method_script_handles(): array {
		wp_register_script(
			'codexawp-po-checkout',
			plugins_url( 'checkout.js', __FILE__ ),
			[ 'wc-blocks-registry', 'wc-settings', 'wp-element' ],
			'1.1.0',
			true
		);
		return [ 'codexawp-po-checkout' ];
	}

	public function get_payment_method_data(): array {
		return [
			'title' => $this->settings['title'] ?? 'Purchase Order',
			'approved' => approved( get_current_user_id() ),
			'supports' => [ 'products' ],
		];
	}
}
