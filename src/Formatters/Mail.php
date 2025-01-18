<?php

namespace Art\AwoocProductOptions\Formatters;

use Art\AwoocProductOptions\Formatter;

class Mail extends Formatter {

	public function format( array $options_data, array $data ): array {

		if ( ! empty( $options_data['options'] ) ) {
			$data['toMail']['options'] = $this->format_options_with_label( $this->format_options_names( $options_data['options'] ) );
		}

		if ( ! empty( $options_data['amount'] ) ) {
			$data['toMail']['price'] = $this->format_price_with_label( $options_data['amount'] );
			$data['toMail']['sum']   = $this->format_sum_with_label( $options_data['amount'], $options_data['quantity'] );
		}

		return $data;
	}


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
