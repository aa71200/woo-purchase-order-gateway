<?php
declare(strict_types=1);

namespace CodexaWP\PurchaseOrder;
defined( 'ABSPATH' ) || exit;

final class Gateway extends \WC_Payment_Gateway {
	public function __construct() {
		$this->id = ID;
		$this->method_title = 'Purchase Order';
		$this->method_description = 'An offline method for approved business accounts.';
		$this->has_fields = true;
		$this->supports = [ 'products' ];
		$this->form_fields = [
			'enabled' => [ 'title' => 'Enable', 'type' => 'checkbox', 'label' => 'Enable purchase orders', 'default' => 'no' ],
			'title'   => [ 'title' => 'Checkout label', 'type' => 'text', 'default' => 'Purchase Order' ],
		];
		$this->init_settings();
		$this->enabled = $this->get_option( 'enabled' );
		$this->title = $this->get_option( 'title' );
		add_action( 'woocommerce_update_options_payment_gateways_' . ID, [ $this, 'process_admin_options' ] );
	}

	public function is_available(): bool {
		return parent::is_available() && approved( get_current_user_id() );
	}

	public function payment_fields(): void {
		echo '<div class="codexawp-po-card"><p class="po-context">Business billing · Merchant review</p>';
		woocommerce_form_field( 'codexawp_po_number', [
			'type' => 'text', 'label' => 'Purchase order reference', 'required' => true,
			'class' => [ 'po-field' ], 'placeholder' => 'e.g. ACME-1042',
			'description' => 'Use the reference issued by your purchasing team.',
		] );
		echo '</div>';
	}

	public function validate_fields(): bool {
		if ( approved( get_current_user_id() ) && null !== $this->submitted_reference() ) {
			return true;
		}
		wc_add_notice( 'Enter a valid purchase order reference using 3–40 letters, digits, slashes or hyphens.', 'error' );
		return false;
	}

	private function submitted_reference(): ?string {
		// WooCommerce checks the checkout nonce or Store API token before invoking the gateway.
		$value = $_POST['codexawp_po_number'] ?? null; // phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return is_string( $value ) ? reference( wp_unslash( $value ) ) : null;
	}

	public function process_payment( $order_id ): array {
		$order = wc_get_order( $order_id );
		$value = $this->submitted_reference();
		$customer_id = get_current_user_id();
		if ( ! $order || $customer_id !== $order->get_customer_id() || 'yes' !== $this->enabled || ! $this->validate_fields() || null === $value ) {
			return [ 'result' => 'failure' ];
		}
		if ( $order->has_status( 'on-hold' ) && $order->get_meta( REFERENCE ) === $value ) {
			return [ 'result' => 'success', 'redirect' => $this->get_return_url( $order ) ];
		}
		if ( ! $order->needs_payment() ) {
			return [ 'result' => 'failure' ];
		}
		$order->update_meta_data( REFERENCE, $value );
		$order->update_status( 'on-hold', 'Purchase order awaiting review.' );
		if ( WC()->cart ) {
			WC()->cart->empty_cart();
		}
		return [ 'result' => 'success', 'redirect' => $this->get_return_url( $order ) ];
	}
}
