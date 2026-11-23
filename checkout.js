'use strict';

const { createElement: h, useEffect, useState } = window.wp.element;
const { __ } = window.wp.i18n;
const poSettings = window.wc.wcSettings.getSetting('codexawp_purchase_order_data', {});
const purchaseOrderLabel = poSettings.title || __('Purchase Order', 'woo-purchase-order-gateway');

function PurchaseOrderInput({ eventRegistration, emitResponse }) {
	const [reference, setReference] = useState('');
	const { onPaymentSetup } = eventRegistration;

	useEffect(() => onPaymentSetup(() => {
		const value = reference.trim();
		if (!/^[A-Za-z0-9][A-Za-z0-9/-]{2,39}$/.test(value)) {
			return { type: emitResponse.responseTypes.ERROR, message: __('Enter a valid purchase order reference.', 'woo-purchase-order-gateway') };
		}
		return {
			type: emitResponse.responseTypes.SUCCESS,
			meta: { paymentMethodData: { codexawp_po_number: value } },
		};
	}), [reference, onPaymentSetup, emitResponse]);

	return h('div', { className: 'codexawp-po-card' },
		h('p', { className: 'po-context' }, __('Business billing · Merchant review', 'woo-purchase-order-gateway')),
		h('label', { className: 'po-field' }, __('Purchase order reference', 'woo-purchase-order-gateway'),
			h('input', { type: 'text', maxLength: 40, placeholder: __('e.g. ACME-1042', 'woo-purchase-order-gateway'), 'aria-describedby': 'codexawp-po-helper', value: reference, onChange: event => setReference(event.target.value) })
		),
		h('p', { id: 'codexawp-po-helper', className: 'po-help' }, __('Use the reference issued by your purchasing team.', 'woo-purchase-order-gateway'))
	);
}

window.wc.wcBlocksRegistry.registerPaymentMethod({
	name: 'codexawp_purchase_order',
	label: purchaseOrderLabel,
	ariaLabel: purchaseOrderLabel,
	content: h(PurchaseOrderInput),
	edit: h('span', null, __('Purchase order reference', 'woo-purchase-order-gateway')),
	canMakePayment: () => poSettings.approved === true,
	supports: { features: poSettings.supports || ['products'] },
});
