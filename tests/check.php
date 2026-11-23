<?php
declare(strict_types=1);

if ( PHP_SAPI !== 'cli' || ! getenv( 'CODEXAWP_TEST_WP_LOAD' ) ) {
	throw new RuntimeException( 'Set CODEXAWP_TEST_WP_LOAD to a disposable WordPress installation.' );
}
require getenv( 'CODEXAWP_TEST_WP_LOAD' );
if ( 'local' !== wp_get_environment_type() || ! function_exists( 'WC' ) ) {
	throw new RuntimeException( 'This fixture requires local WordPress with WooCommerce active.' );
}

function check( bool $ok, string $message ): void {
	if ( ! $ok ) {
		throw new RuntimeException( $message );
	}
	echo "PASS: $message\n";
}

$uid = wp_insert_user( [ 'user_login' => 'po-' . wp_generate_password( 12, false ), 'user_pass' => wp_generate_password(), 'role' => 'customer' ] );
$order = wc_create_order( [ 'customer_id' => $uid ] );
$order->set_total( 19 );
$order->set_payment_method( 'codexawp_purchase_order' );
$order->save();
wp_set_current_user( $uid );
update_option( 'woocommerce_codexawp_purchase_order_settings', [ 'enabled' => 'yes', 'title' => 'Purchase Order' ] );
WC()->payment_gateways();
$gateway = new \CodexaWP\PurchaseOrder\Gateway();
check( ! $gateway->is_available(), 'Unapproved business account cannot access gateway' );
update_user_meta( $uid, '_codexawp_po_approved', 'yes' );
check( $gateway->is_available(), 'Approved account can access gateway' );
check( null === \CodexaWP\PurchaseOrder\reference( '!' ), 'Invalid reference rejected' );
$_POST['codexawp_po_number'] = 'ACME-PO-109';
check( 'success' === $gateway->process_payment( $order->get_id() )['result'], 'Offline purchase order accepted' );
$stored = wc_get_order( $order->get_id() );
check( 'ACME-PO-109' === $stored->get_meta( '_codexawp_po_reference' ) && $stored->has_status( 'on-hold' ), 'Order metadata and review status persisted' );
check( 'success' === $gateway->process_payment( $order->get_id() )['result'], 'Resubmission reuses the existing order state' );
echo 'Fixture order: ' . $order->get_id() . PHP_EOL;
