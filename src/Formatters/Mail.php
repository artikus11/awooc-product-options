<?php

namespace Art\AwoocProductOptions\Formatters;

use Art\AwoocProductOptions\Formatter;

class Mail extends Formatter {

	public function format_price_with_label( float $amount ): string {

		return sprintf(
			'%s%s',
			__( 'Price: ', 'art-woocommerce-order-one-click' ),
			wp_filter_nohtml_kses( $this->format_price( $amount ) )
		);
	}


	public function format_sum_with_label( float $amount, int $qty ): string {

		return sprintf(
			'%s%s',
			__( 'Amount: ', 'art-woocommerce-order-one-click' ),
			wp_filter_nohtml_kses( $this->format_sum( $amount, $qty ) )
		);
	}


	public function format_options_with_label( array $options_names ): string {

		return sprintf(
		/* translators: %s: options names */
			__( 'Options: %s', 'awooc-product-options' ),
			wp_strip_all_tags( $this->format_options_list( $options_names ) )
		);
	}
}
