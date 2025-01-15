<?php

namespace Art\AwoocProductOptions\Formatters;

use Art\AwoocProductOptions\Formatter;

class Popup extends Formatter {

	public function format_price_with_label( float $amount ): string {

		return sprintf(
			'<span class="awooc-price-label">%s</span><span class="awooc-price-value">%s</span>',
			apply_filters( 'awooc_popup_price_label', __( 'Price: ', 'art-woocommerce-order-one-click' ) ),
			$this->format_price( $amount )
		);
	}


	public function format_sum_with_label( float $amount, int $qty ): string {

		return sprintf(
			'<span class="awooc-sum-label">%s</span><span class="awooc-sum-value">%s</span>',
			apply_filters( 'awooc_popup_sum_label', __( 'Amount: ', 'art-woocommerce-order-one-click' ) ),
			$this->format_sum( $amount, $qty )
		);
	}


	public function format_options_with_label( array $options_names ): string {

		return sprintf(
			'<span class="awooc-option-label">%s</span></br><span class="awooc-option-value"><span>%s</span></span>',
			apply_filters( 'awooc_popup_options_label', esc_html__( 'Options: ', 'awooc-product-options' ) ),
			implode( '; </span><span>', $options_names )
		);
	}
}