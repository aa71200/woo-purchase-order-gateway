<?php
/**
 * Plugin Name: CodexaWP Purchase Order Gateway
 * Description: Offline purchase order checkout for approved business customers.
 * Version: 1.0.0
 * Requires Plugins: woocommerce
 * Requires PHP: 8.2
 * License: GPL-2.0-or-later
 */
declare(strict_types=1);

namespace CodexaWP\PurchaseOrder;

defined( 'ABSPATH' ) || exit;
const ID = 'codexawp_purchase_order';
const APPROVAL = '_codexawp_po_approved';
const REFERENCE = '_codexawp_po_reference';

add_action( 'wp_enqueue_scripts', static function (): void {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_enqueue_style( 'codexawp-po-checkout', plugins_url( 'checkout.css', __FILE__ ), [], '1.1.0' );
	}
} );

function approved( int $user_id ): bool {
	return $user_id > 0 && 'yes' === get_user_meta( $user_id, APPROVAL, true );
}

function reference( mixed $input ): ?string {
	if ( ! is_string( $input ) ) {
		return null;
	}
	$value = trim( $input );
	return preg_match( '/\A[A-Za-z0-9][A-Za-z0-9\/-]{2,39}\z/', $value ) ? $value : null;
}

add_action( 'before_woocommerce_init', static function (): void {
	$features = \Automattic\WooCommerce\Utilities\FeaturesUtil::class;
	$features::declare_compatibility( 'custom_order_tables', __FILE__, true );
	$features::declare_compatibility( 'cart_checkout_blocks', __FILE__, true );
} );

add_filter( 'woocommerce_payment_gateways', static function ( array $gateways ): array {
	require_once __DIR__ . '/class-gateway.php';
	$gateways[] = Gateway::class;
	return $gateways;
} );

add_action( 'woocommerce_blocks_loaded', static function (): void {
	add_action( 'woocommerce_blocks_payment_method_type_registration', static function ( $registry ): void {
		require_once __DIR__ . '/class-blocks.php';
		$registry->register( new Blocks() );
	} );
} );

add_action( 'woocommerce_admin_order_data_after_billing_address', static function ( \WC_Order $order ): void {
	$value = $order->get_meta( REFERENCE );
	if ( $value ) {
		echo '<p class="codexawp-po-reference"><strong>Purchase order reference</strong><br>' . esc_html( $value ) . '</p>';
	}
} );
