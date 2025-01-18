<?php

namespace Art\AwoocProductOptions;

use Art\AwoocProductOptions\Formatters\Analytics;
use Art\AwoocProductOptions\Formatters\Mail;
use Art\AwoocProductOptions\Formatters\Popup;

class HookHandler {

	public function init_hooks(): void {

		add_filter( 'awooc_data_ajax', [ $this, 'add_data_ajax' ], 10, 2 );
	}


	public function add_data_ajax( $data, $product ) {

		$product_id = $this->get_product_id( $product );

		$options_data = apply_filters( 'awooc_data_ajax_options', [], $product_id );

		if ( empty( $options_data['options'] ) ) {
			return $data;
		}

		return $this->format_data( $options_data, $data );
	}


	protected function get_product_id( $product ) {

		return $product->get_parent_id() ? : $product->get_id();
	}


	protected function format_data( array $options_data, array $data ): array {

		$data = ( new Popup() )->format( $options_data, $data );
		$data = ( new Mail() )->format( $options_data, $data );
		$data = ( new Analytics() )->format( $options_data, $data );

		$data['toOrder']['options'] = $options_data['options'];

		return $data;
	}
}
