<?php

namespace Art\AwoocProductOptions\Formatters;

use Art\AwoocProductOptions\Formatter;

class Popup extends Formatter {

	public function format( array $options_data, array $data ): array {

		if ( ! empty( $options_data['options'] ) ) {
			$data['toPopup']['options'] = $this->format_options_with_label( $this->format_options_names( $options_data['options'] ) );
		}

		if ( ! empty( $options_data['amount'] ) ) {
			$data['toPopup']['price'] = $this->format_price_with_label( $options_data['amount'] );
			$data['toPopup']['sum']   = $this->format_sum_with_label( $options_data['amount'], $options_data['quantity'] );
		}

		return $data;
	}


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
			wp_strip_all_tags( implode( '; </span><span>', $options_names ) )
		);
	}
}
